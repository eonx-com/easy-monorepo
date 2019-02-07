<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Bridge\Laravel\Providers;

use Closure;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\Pagination\Interfaces\StartSizeDataInterface;
use StepTheFkUp\Pagination\Interfaces\StartSizeDataResolverInterface;
use StepTheFkUp\Pagination\Resolvers\Config\StartSizeConfig;
use StepTheFkUp\Psr7Factory\Interfaces\Psr7FactoryInterface;
use StepTheFkUp\Psr7Factory\Psr7Factory;

abstract class AbstractStartSizePaginationProvider extends ServiceProvider
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
     * Register start_size pagination services.
     *
     * @return void
     */
    public function register(): void
    {
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
     * @return \StepTheFkUp\Pagination\Resolvers\Config\StartSizeConfig
     */
    protected function createConfig(): StartSizeConfig
    {
        $config = $this->app->get('config')->get('pagination.start_size', []);

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
     */
    protected function createServerRequest(): ServerRequestInterface
    {
        return $this->app->get(Psr7FactoryInterface::class)->createRequest($this->app->get('request'));
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
