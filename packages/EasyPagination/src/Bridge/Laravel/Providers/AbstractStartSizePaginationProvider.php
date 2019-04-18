<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPagination\Bridge\Laravel\Providers;

use Closure;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\EasyPagination\Interfaces\StartSizeDataInterface;
use StepTheFkUp\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use StepTheFkUp\EasyPagination\Resolvers\Config\StartSizeConfig;

abstract class AbstractStartSizeEasyPaginationProvider extends ServiceProvider
{
    /**
     * Default start_size pagination config used when app config not set.
     *
     * @var mixed[]
     */
    protected static $defaultConfig = [
        'start_attribute' => 'page',
        'start_default' => 1,
        'size_attribute' => 'perPage',
        'size_default' => 15
    ];

    /**
     * Publish configuration file.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/pagination.php' => \base_path('config/pagination.php')
        ]);
    }

    /**
     * Register start_size pagination services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/pagination.php', 'pagination');

        $this->app->bind(StartSizeDataResolverInterface::class, $this->getResolverClosure());
        $this->app->bind(StartSizeDataInterface::class, $this->getDataClosure());
    }

    /**
     * Get closure to instantiate the implementation of StartSizeDataResolverInterface.
     *
     * @return \Closure
     */
    abstract protected function getResolverClosure(): Closure;

    /**
     * Create start_size pagination config from app config.
     *
     * @return \StepTheFkUp\EasyPagination\Resolvers\Config\StartSizeConfig
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
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

    /**
     * Get server request created from application request.
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createServerRequest(): ServerRequestInterface
    {
        return $this->app->make(ServerRequestInterface::class);
    }

    /**
     * Get closure to instantiate the page pagination data.
     *
     * @return \Closure
     */
    private function getDataClosure(): Closure
    {
        return function (): StartSizeDataInterface {
            return $this->app->get(StartSizeDataResolverInterface::class)->resolve($this->createServerRequest());
        };
    }
}

\class_alias(
    AbstractStartSizeEasyPaginationProvider::class,
    'LoyaltyCorp\EasyPagination\Bridge\Laravel\Providers\AbstractStartSizeEasyPaginationProvider',
    false
);
