<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Laravel\Providers;

use EonX\EasyPagination\Bridge\Laravel\Listeners\FromRequestPaginationListener;
use EonX\EasyPagination\Bridge\Laravel\Middleware\PaginationFromRequestMiddleware;
use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Interfaces\PaginationProviderInterface;
use EonX\EasyPagination\PaginationConfig;
use EonX\EasyPagination\PaginationProvider;
use Illuminate\Contracts\Container\Container;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

final class EasyPaginationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/easy-pagination.php' => \base_path('config/easy-pagination.php'),
        ]);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/easy-pagination.php', 'easy-pagination');

        $this->registerPaginationProvider();
        $this->registerDefaultResolver();
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function registerDefaultResolver(): void
    {
        if (\config('easy-pagination.user_default_resolver', true) === false) {
            return;
        }

        // Lumen
        if ($this->app instanceof LumenApplication) {
            $this->app->singleton(
                PaginationFromRequestMiddleware::class,
                static function (Container $app): PaginationFromRequestMiddleware {
                    return new PaginationFromRequestMiddleware($app->make(PaginationProviderInterface::class));
                }
            );
            $this->app->middleware([PaginationFromRequestMiddleware::class]);

            return;
        }

        // Laravel
        $this->app->singleton(
            FromRequestPaginationListener::class,
            static function (Container $app): FromRequestPaginationListener {
                return new FromRequestPaginationListener($app->make(PaginationProviderInterface::class));
            }
        );
        $this->app->make('events')
            ->listen(RouteMatched::class, FromRequestPaginationListener::class);
    }

    private function registerPaginationProvider(): void
    {
        $this->app->singleton(
            PaginationProviderInterface::class,
            static function (): PaginationProviderInterface {
                $config = new PaginationConfig(
                    \config('easy-pagination.pagination.page_attribute'),
                    (int)\config('easy-pagination.pagination.page_default'),
                    \config('easy-pagination.pagination.per_page_attribute'),
                    (int)\config('easy-pagination.pagination.per_page_default')
                );

                return new PaginationProvider($config);
            }
        );

        $this->app->singleton(PaginationInterface::class, static function (Container $app): PaginationInterface {
            return $app->make(PaginationProviderInterface::class)->getPagination();
        });
    }
}
