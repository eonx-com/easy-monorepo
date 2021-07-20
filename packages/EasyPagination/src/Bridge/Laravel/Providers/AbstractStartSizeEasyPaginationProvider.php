<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Laravel\Providers;

use Closure;
use EonX\EasyPagination\Factories\StartSizeDataFactory;
use EonX\EasyPagination\Interfaces\StartSizeDataFactoryInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use EonX\EasyPagination\Resolvers\Config\StartSizeConfig;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

/**
 * @deprecated since 3.2, will be removed in 4.0. Use EasyPaginationServiceProvider instead.
 */
abstract class AbstractStartSizeEasyPaginationProvider extends ServiceProvider
{
    /**
     * @var mixed[]
     */
    protected static $defaultConfig = [
        'start_attribute' => 'page',
        'start_default' => 1,
        'size_attribute' => 'perPage',
        'size_default' => 15,
    ];

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/pagination.php' => \base_path('config/pagination.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/pagination.php', 'pagination');

        $this->app->singleton(StartSizeDataResolverInterface::class, $this->getResolverClosure());
        $this->app->singleton(StartSizeDataInterface::class, $this->getDataClosure());
        $this->app->singleton(StartSizeDataFactoryInterface::class, StartSizeDataFactory::class);
    }

    abstract protected function getResolverClosure(): Closure;

    protected static function createConfig(): StartSizeConfig
    {
        $config = \config('pagination.start_size', []);

        return new StartSizeConfig(
            $config['start_attribute'] ?? static::$defaultConfig['start_attribute'],
            $config['start_default'] ?? static::$defaultConfig['start_default'],
            $config['size_attribute'] ?? static::$defaultConfig['size_attribute'],
            $config['size_default'] ?? static::$defaultConfig['size_default']
        );
    }

    private function getDataClosure(): Closure
    {
        return static function (Container $app): StartSizeDataInterface {
            /** @var \Illuminate\Contracts\Foundation\Application $app */
            $request = $app->get($app->runningInConsole() ? 'request' : Request::class);

            return $app->get(StartSizeDataResolverInterface::class)->resolve($request);
        };
    }
}
