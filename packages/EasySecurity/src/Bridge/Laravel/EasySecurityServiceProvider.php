<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel;

use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface;
use EonX\EasySecurity\Authorization\AuthorizationMatrixFactory;
use EonX\EasySecurity\Bridge\Laravel\Helpers\DeferredContextResolver;
use EonX\EasySecurity\Bridge\Laravel\Interfaces\DeferredContextResolverInterface;
use EonX\EasySecurity\Bridge\TagsInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\SecurityContextFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use EonX\EasySecurity\SecurityContextResolver;
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

        // Authorization
        $this->app->singleton(
            AuthorizationMatrixFactoryInterface::class,
            function (): AuthorizationMatrixFactoryInterface {
                return new AuthorizationMatrixFactory(
                    $this->app->tagged(TagsInterface::TAG_ROLES_PROVIDER),
                    $this->app->tagged(TagsInterface::TAG_PERMISSIONS_PROVIDER)
                );
            }
        );
        $this->app->singleton(AuthorizationMatrixInterface::class, function (): AuthorizationMatrixInterface {
            return $this->app->get(AuthorizationMatrixFactoryInterface::class)->create();
        });

        $this->app->singleton($contextServiceId, function (): SecurityContextInterface {
            return $this->app->get(SecurityContextFactoryInterface::class)->create();
        });
        $this->app->alias($contextServiceId, ContextInterface::class);
        $this->app->alias($contextServiceId, SecurityContextInterface::class);

        $this->app->singleton(SecurityContextResolverInterface::class, SecurityContextResolver::class);
        $this->app
            ->when(SecurityContextResolver::class)
            ->needs('$contextModifiers')
            ->give(function (): iterable {
                return $this->app->tagged(TagsInterface::TAG_CONTEXT_MODIFIER);
            });
        $this->app
            ->when(SecurityContextResolver::class)
            ->needs('$contextConfigurators')
            ->give(function (): iterable {
                return $this->app->tagged(TagsInterface::TAG_CONTEXT_CONFIGURATOR);
            });

        $this->app->singleton(DeferredContextResolverInterface::class, DeferredContextResolver::class);
        $this->app
            ->when(DeferredContextResolver::class)
            ->needs('$contextServiceId')
            ->give($contextServiceId);

        $this->app->singleton(EasyApiTokenDecoderInterface::class, function (): EasyApiTokenDecoderInterface {
            return $this->app->get(EasyApiTokenDecoderFactoryInterface::class)->build(
                \config('easy-security.token_decoder')
            );
        });
    }
}
