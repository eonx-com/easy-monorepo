<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Laravel\Providers;

use Closure;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use EonX\EasyPagination\Resolvers\Config\StartSizeConfig;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\ServerRequestInterface;

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

        $this->app->bind(StartSizeDataResolverInterface::class, $this->getResolverClosure());
        $this->app->bind(StartSizeDataInterface::class, $this->getDataClosure());
    }

    abstract protected function getResolverClosure(): Closure;

    protected function createConfig(): StartSizeConfig
    {
        $config = $this->app->make('config')->get('pagination.start_size', []);

        return new StartSizeConfig(
            $config['start_attribute'] ?? static::$defaultConfig['start_attribute'],
            $config['start_default'] ?? static::$defaultConfig['start_default'],
            $config['size_attribute'] ?? static::$defaultConfig['size_attribute'],
            $config['size_default'] ?? static::$defaultConfig['size_default']
        );
    }

    protected function createServerRequest(): ServerRequestInterface
    {
        return $this->app->make(ServerRequestInterface::class);
    }

    private function getDataClosure(): Closure
    {
        return function (): StartSizeDataInterface {
            return $this->app->get(StartSizeDataResolverInterface::class)->resolve($this->createServerRequest());
        };
    }
}
