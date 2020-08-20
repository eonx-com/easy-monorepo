<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel;

use EonX\EasySecurity\Authorization\AuthorizationMatrixFactory;
use EonX\EasySecurity\Bridge\BridgeConstantsInterface;
use EonX\EasySecurity\DeferredSecurityContextProvider;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\DeferredSecurityContextProviderInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\MainSecurityContextConfigurator;
use EonX\EasySecurity\SecurityContext;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

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
        $this->registerSecurityContext($contextServiceId);
        $this->registerDeferredSecurityContextProvider($contextServiceId);
    }

    private function registerAuthorizationMatrix(): void
    {
        $this->app->singleton(
            AuthorizationMatrixFactoryInterface::class,
            function (): AuthorizationMatrixFactoryInterface {
                return new AuthorizationMatrixFactory(
                    $this->app->tagged(BridgeConstantsInterface::TAG_ROLES_PROVIDER),
                    $this->app->tagged(BridgeConstantsInterface::TAG_PERMISSIONS_PROVIDER)
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

    private function registerSecurityContext(string $contextServiceId): void
    {
        // Use container to inject constructor arguments :)
        $this->app->singleton(MainSecurityContextConfigurator::class);

        $this->app->singleton($contextServiceId, function (): SecurityContextInterface {
            return new SecurityContext();
        });

        $extend = function (SecurityContextInterface $securityContext): SecurityContextInterface {
            /** @var \EonX\EasySecurity\MainSecurityContextConfigurator $configurator */
            $configurator = $this->app->make(MainSecurityContextConfigurator::class);

            $configurator
                ->withConfigurators($this->app->tagged(BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR))
                ->withModifiers($this->app->tagged(BridgeConstantsInterface::TAG_CONTEXT_MODIFIER))
                ->withTokenDecoder(\config('easy-security.token_decoder', null));

            return $configurator->configure($securityContext, $this->app->make(Request::class));
        };

        $this->app->extend($contextServiceId, $extend);
    }
}
