<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Laravel;

use EonX\EasyPagination\Laravel\Listeners\PaginationFromRequestListener;
use EonX\EasyPagination\Laravel\Middleware\PaginationFromRequestMiddleware;
use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyPagination\Provider\PaginationConfigProvider;
use EonX\EasyPagination\Provider\PaginationProvider;
use EonX\EasyPagination\Provider\PaginationProviderInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

final class EasyPaginationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-pagination.php' => \base_path('config/easy-pagination.php'),
        ]);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-pagination.php', 'easy-pagination');

        $this->registerPaginationProvider();
        $this->registerDefaultResolver();
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function registerDefaultResolver(): void
    {
        // "user_default_resolver" is a misspelled key read before, still honoured for apps that set it
        if (\config('easy-pagination.use_default_resolver', true) === false
            || \config('easy-pagination.user_default_resolver', true) === false) {
            return;
        }

        // Lumen
        if ($this->app instanceof LumenApplication) {
            $this->app->singleton(
                PaginationFromRequestMiddleware::class,
                static fn (Container $app): PaginationFromRequestMiddleware => new PaginationFromRequestMiddleware(
                    $app->make(PaginationProviderInterface::class)
                )
            );
            $this->app->middleware([PaginationFromRequestMiddleware::class]);

            return;
        }

        // Laravel
        $this->app->singleton(
            PaginationFromRequestListener::class,
            static fn (Container $app): PaginationFromRequestListener => new PaginationFromRequestListener(
                $app->make(PaginationProviderInterface::class)
            )
        );
        $this->app->make('events')
            ->listen(RouteMatched::class, PaginationFromRequestListener::class);
    }

    private function registerPaginationProvider(): void
    {
        $this->app->singleton(
            PaginationProviderInterface::class,
            static function (): PaginationProviderInterface {
                $perPageMax = \config('easy-pagination.pagination.per_page_max');
                $paginationConfigProvider = new PaginationConfigProvider(
                    \config('easy-pagination.pagination.page_attribute'),
                    (int)\config('easy-pagination.pagination.page_default'),
                    \config('easy-pagination.pagination.per_page_attribute'),
                    (int)\config('easy-pagination.pagination.per_page_default'),
                    $perPageMax === null || $perPageMax === '' ? null : (int)$perPageMax
                );

                return new PaginationProvider($paginationConfigProvider);
            }
        );

        $this->app->singleton(
            PaginationInterface::class,
            static fn (Container $app): PaginationInterface => $app->make(PaginationProviderInterface::class)
                ->getPagination()
        );
    }
}
