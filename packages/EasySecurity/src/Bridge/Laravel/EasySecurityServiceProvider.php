<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel;

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface;
use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface as EasyBugsnagBridgeConstantsInterface;
use EonX\EasySecurity\Authorization\AuthorizationMatrixFactory;
use EonX\EasySecurity\Authorization\CachedAuthorizationMatrixFactory;
use EonX\EasySecurity\Bridge\BridgeConstantsInterface;
use EonX\EasySecurity\Bridge\EasyBugsnag\SecurityContextClientConfigurator;
use EonX\EasySecurity\Bridge\Laravel\Listeners\ConfigureSecurityContextListener;
use EonX\EasySecurity\Configurators\ApiTokenConfigurator;
use EonX\EasySecurity\Configurators\AuthorizationMatrixConfigurator;
use EonX\EasySecurity\DeferredSecurityContextProvider;
use EonX\EasySecurity\Events\SecurityContextCreatedEvent;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\DeferredSecurityContextProviderInterface;
use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface;
use EonX\EasySecurity\Interfaces\SecurityContextFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\SecurityContextFactory;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class EasySecurityServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-security.php' => \base_path('config/easy-security.php'),
        ]);

        $this->app->make('events')
            ->listen(SecurityContextCreatedEvent::class, ConfigureSecurityContextListener::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-security.php', 'easy-security');

        $contextServiceId = \config('easy-security.context_service_id');

        $this->registerApiTokenDecoder();
        $this->registerAuthorizationMatrix();
        $this->registerDefaultConfigurators();
        $this->registerEasyBugsnag();
        $this->registerListeners();
        $this->registerSecurityContext($contextServiceId);
        $this->registerDeferredSecurityContextProvider($contextServiceId);
    }

    private function registerApiTokenDecoder(): void
    {
        $this->app->singleton(
            BridgeConstantsInterface::SERVICE_API_TOKEN_DECODER,
            function (): ApiTokenDecoderInterface {
                return $this->app
                    ->make(ApiTokenDecoderFactoryInterface::class)
                    ->build(\config('easy-security.token_decoder'));
            }
        );
    }

    private function registerAuthorizationMatrix(): void
    {
        $this->app->singleton(BridgeConstantsInterface::SERVICE_AUTHORIZATION_MATRIX_CACHE, ArrayAdapter::class);

        $this->app->singleton(
            AuthorizationMatrixFactoryInterface::class,
            function (): AuthorizationMatrixFactoryInterface {
                return new CachedAuthorizationMatrixFactory(
                    $this->app->make(BridgeConstantsInterface::SERVICE_AUTHORIZATION_MATRIX_CACHE),
                    new AuthorizationMatrixFactory(
                        $this->app->tagged(BridgeConstantsInterface::TAG_ROLES_PROVIDER),
                        $this->app->tagged(BridgeConstantsInterface::TAG_PERMISSIONS_PROVIDER)
                    )
                );
            }
        );

        $this->app->singleton(AuthorizationMatrixInterface::class, function (): AuthorizationMatrixInterface {
            return $this->app->get(AuthorizationMatrixFactoryInterface::class)->create();
        });
    }

    private function registerDefaultConfigurators(): void
    {
        if (\config('easy-security.use_default_configurators', true) === false) {
            return;
        }

        $this->app->singleton(ApiTokenConfigurator::class, function (): ApiTokenConfigurator {
            return new ApiTokenConfigurator(
                $this->app->make(BridgeConstantsInterface::SERVICE_API_TOKEN_DECODER),
                SecurityContextConfiguratorInterface::SYSTEM_PRIORITY
            );
        });

        $this->app->singleton(AuthorizationMatrixConfigurator::class, function (): AuthorizationMatrixConfigurator {
            return new AuthorizationMatrixConfigurator(
                $this->app->make(AuthorizationMatrixInterface::class),
                SecurityContextConfiguratorInterface::SYSTEM_PRIORITY
            );
        });

        $this->app->tag(
            [ApiTokenConfigurator::class, AuthorizationMatrixConfigurator::class],
            [BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR]
        );
    }

    private function registerDeferredSecurityContextProvider(string $contextServiceId): void
    {
        $this->app->singleton(
            DeferredSecurityContextProviderInterface::class,
            function () use ($contextServiceId): DeferredSecurityContextProviderInterface {
                return new DeferredSecurityContextProvider($this->app, $contextServiceId);
            }
        );
    }

    private function registerEasyBugsnag(): void
    {
        if (\config('easy-security.easy_bugsnag', false) === false
            || \interface_exists(EasyBugsnagBridgeConstantsInterface::class) === false) {
            return;
        }

        $this->app->singleton(
            SecurityContextClientConfigurator::class,
            function (): SecurityContextClientConfigurator {
                return new SecurityContextClientConfigurator(
                    $this->app->make(DeferredSecurityContextProviderInterface::class)
                );
            }
        );
        $this->app->tag(
            SecurityContextClientConfigurator::class,
            [EasyBugsnagBridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR]
        );
    }

    private function registerListeners(): void
    {
        $this->app->singleton(ConfigureSecurityContextListener::class, function (): ConfigureSecurityContextListener {
            return new ConfigureSecurityContextListener(
                $this->app->tagged(BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR),
                $this->app->make(Request::class)
            );
        });
    }

    private function registerSecurityContext(string $contextServiceId): void
    {
        // SecurityContextFactory
        $this->app->singleton(SecurityContextFactoryInterface::class, SecurityContextFactory::class);

        // SecurityContext
        $this->app->singleton($contextServiceId, function (): SecurityContextInterface {
            return $this->app->make(SecurityContextFactoryInterface::class)->create();
        });
    }
}
