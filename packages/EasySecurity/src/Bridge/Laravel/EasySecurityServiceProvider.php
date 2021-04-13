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
use Illuminate\Contracts\Container\Container;
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
            static function (Container $app): ApiTokenDecoderInterface {
                return $app
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
            static function (Container $app): AuthorizationMatrixFactoryInterface {
                return new CachedAuthorizationMatrixFactory(
                    $app->make(BridgeConstantsInterface::SERVICE_AUTHORIZATION_MATRIX_CACHE),
                    new AuthorizationMatrixFactory(
                        $app->tagged(BridgeConstantsInterface::TAG_ROLES_PROVIDER),
                        $app->tagged(BridgeConstantsInterface::TAG_PERMISSIONS_PROVIDER)
                    )
                );
            }
        );

        $this->app->singleton(
            AuthorizationMatrixInterface::class,
            static function (Container $app): AuthorizationMatrixInterface {
                return $app->get(AuthorizationMatrixFactoryInterface::class)->create();
            }
        );
    }

    private function registerDefaultConfigurators(): void
    {
        if (\config('easy-security.use_default_configurators', true) === false) {
            return;
        }

        $this->app->singleton(ApiTokenConfigurator::class, static function (Container $app): ApiTokenConfigurator {
            return new ApiTokenConfigurator(
                $app->make(BridgeConstantsInterface::SERVICE_API_TOKEN_DECODER),
                SecurityContextConfiguratorInterface::SYSTEM_PRIORITY
            );
        });

        $this->app->singleton(
            AuthorizationMatrixConfigurator::class,
            static function (Container $app): AuthorizationMatrixConfigurator {
                return new AuthorizationMatrixConfigurator(
                    $app->make(AuthorizationMatrixInterface::class),
                    SecurityContextConfiguratorInterface::SYSTEM_PRIORITY
                );
            }
        );

        $this->app->tag(
            [ApiTokenConfigurator::class, AuthorizationMatrixConfigurator::class],
            [BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR]
        );
    }

    private function registerDeferredSecurityContextProvider(string $contextServiceId): void
    {
        $this->app->singleton(
            DeferredSecurityContextProviderInterface::class,
            static function (Container $app) use ($contextServiceId): DeferredSecurityContextProviderInterface {
                return new DeferredSecurityContextProvider($app, $contextServiceId);
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
            static function (Container $app): SecurityContextClientConfigurator {
                return new SecurityContextClientConfigurator(
                    $app->make(DeferredSecurityContextProviderInterface::class)
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
        $this->app->singleton(
            ConfigureSecurityContextListener::class,
            static function (Container $app): ConfigureSecurityContextListener {
                return new ConfigureSecurityContextListener(
                    $app->tagged(BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR),
                    $app->make(Request::class)
                );
            }
        );
    }

    private function registerSecurityContext(string $contextServiceId): void
    {
        // SecurityContextFactory
        $this->app->singleton(SecurityContextFactoryInterface::class, SecurityContextFactory::class);

        // SecurityContext
        $this->app->singleton($contextServiceId, static function (Container $app): SecurityContextInterface {
            return $app->make(SecurityContextFactoryInterface::class)->create();
        });
    }
}
