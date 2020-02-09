<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel;

use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface;
use EonX\EasySecurity\Bridge\Laravel\Helpers\DeferredContextResolver;
use EonX\EasySecurity\Bridge\Laravel\Interfaces\DeferredContextResolverInterface;
use EonX\EasySecurity\Bridge\TagsInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolverInterface;
use EonX\EasySecurity\Resolvers\ContextResolver;
use Illuminate\Support\ServiceProvider;

final class EasySecurityServiceProvider extends ServiceProvider
{
    /**
     * Publish configuration file.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-security.php' => \base_path('config/easy-security.php')
        ]);
    }

    /**
     * Register easy-security services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-security.php', 'easy-security');

        $this->app->singleton(ContextResolverInterface::class, ContextResolver::class);
        $this->app
            ->when(ContextResolver::class)
            ->needs('$contextDataResolvers')
            ->give(function (): iterable {
                return $this->app->tagged(TagsInterface::TAG_CONTEXT_DATA_RESOLVER);
            });

        $this->app->singleton(DeferredContextResolverInterface::class, DeferredContextResolver::class);
        $this->app
            ->when(DeferredContextResolver::class)
            ->needs('$contextServiceId')
            ->give(\config('easy-security.context_service_id'));

        $this->app->singleton(EasyApiTokenDecoderInterface::class, function (): EasyApiTokenDecoderInterface {
            return $this->app->get(EasyApiTokenDecoderFactoryInterface::class)->build(
                \config('easy-security.token_decoder')
            );
        });
    }
}
