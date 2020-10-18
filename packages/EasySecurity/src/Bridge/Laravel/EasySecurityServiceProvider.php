<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel;

use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface;
use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface as EasyBugsnagBridgeConstantsInterface;
use EonX\EasySecurity\Authorization\AuthorizationMatrixFactory;
use EonX\EasySecurity\Authorization\CachedAuthorizationMatrixFactory;
use EonX\EasySecurity\Bridge\BridgeConstantsInterface;
use EonX\EasySecurity\Bridge\EasyBugsnag\SecurityContextClientConfigurator;
use EonX\EasySecurity\DeferredSecurityContextProvider;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\DeferredSecurityContextProviderInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\MainSecurityContextConfigurator;
use EonX\EasySecurity\SecurityContext;
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
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-security.php', 'easy-security');

        $contextServiceId = \config('easy-security.context_service_id');

        $this->registerAuthorizationMatrix();
        $this->registerEasyBugsnag($contextServiceId);
        $this->registerSecurityContext($contextServiceId);
        $this->registerDeferredSecurityContextProvider($contextServiceId);
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

    private function registerDeferredSecurityContextProvider(string $contextServiceId): void
    {
        $this->app->singleton(
            DeferredSecurityContextProviderInterface::class,
            function () use ($contextServiceId): DeferredSecurityContextProviderInterface {
                return new DeferredSecurityContextProvider($this->app, $contextServiceId);
            }
        );
    }

    private function registerEasyBugsnag(string $contextServiceId): void
    {
        if (\config('easy-security.easy_bugsnag', false) === false
            || \interface_exists(EasyBugsnagBridgeConstantsInterface::class) === false) {
            return;
        }

        $this->app->singleton(
            SecurityContextClientConfigurator::class,
            function () use ($contextServiceId): SecurityContextClientConfigurator {
                return new SecurityContextClientConfigurator($this->app->make($contextServiceId));
            }
        );
        $this->app->tag(
            SecurityContextClientConfigurator::class,
            [EasyBugsnagBridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR]
        );
    }

    private function registerSecurityContext(string $contextServiceId): void
    {
        $this->app->singleton(MainSecurityContextConfigurator::class, function (): MainSecurityContextConfigurator {
            $request = $this->app->make(Request::class);
            $apiTokenDecoderFactory = $this->app->make(ApiTokenDecoderFactoryInterface::class);
            $apiTokenDecoder = $apiTokenDecoderFactory->build(\config('easy-security.token_decoder', null));

            $mainConfigurator = new MainSecurityContextConfigurator(
                $this->app->make(AuthorizationMatrixInterface::class),
                $request,
                $apiTokenDecoder->decode($request)
            );

            return $mainConfigurator
                ->withConfigurators($this->app->tagged(BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR))
                ->withModifiers($this->app->tagged(BridgeConstantsInterface::TAG_CONTEXT_MODIFIER));
        });

        $this->app->singleton($contextServiceId, function (): SecurityContextInterface {
            return new SecurityContext();
        });

        $extend = function (SecurityContextInterface $securityContext): SecurityContextInterface {
            /** @var \EonX\EasySecurity\MainSecurityContextConfigurator $configurator */
            $configurator = $this->app->make(MainSecurityContextConfigurator::class);

            return $configurator->configure($securityContext);
        };

        $this->app->extend($contextServiceId, $extend);
    }
}
