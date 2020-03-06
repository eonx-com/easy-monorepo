<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel;

use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface;
use EonX\EasySecurity\Bridge\Laravel\Helpers\DeferredContextResolver;
use EonX\EasySecurity\Bridge\Laravel\Interfaces\DeferredContextResolverInterface;
use EonX\EasySecurity\Bridge\TagsInterface;
use EonX\EasySecurity\ContextResolver;
use EonX\EasySecurity\Interfaces\ContextFactoryInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\ContextResolverInterface;
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

        $this->app->singleton($contextServiceId, function (): ContextInterface {
            return $this->app->get(ContextFactoryInterface::class)->create();
        });
        $this->app->alias($contextServiceId, ContextInterface::class);

        $this->app->singleton(ContextResolverInterface::class, ContextResolver::class);
        $this->app
            ->when(ContextResolver::class)
            ->needs('$contextModifiers')
            ->give(function (): iterable {
                return $this->app->tagged(TagsInterface::TAG_CONTEXT_MODIFIER);
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
