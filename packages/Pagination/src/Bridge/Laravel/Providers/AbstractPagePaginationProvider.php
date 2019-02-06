<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Bridge\Laravel\Providers;

use Closure;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\Pagination\Interfaces\PagePaginationDataInterface;
use StepTheFkUp\Pagination\Interfaces\PagePaginationDataResolverInterface;
use StepTheFkUp\Pagination\Resolvers\Config\PagePaginationConfig;
use StepTheFkUp\Psr7Factory\Psr7Factory;

abstract class AbstractPagePaginationProvider extends ServiceProvider
{
    /**
     * Default page pagination config used when app config not set.
     *
     * @var mixed[]
     */
    protected static $defaultConfig = [
        'number_attr' => 'page',
        'number_default' => 1,
        'size_attr' => 'perPage',
        'size_default' => 15
    ];

    /**
     * Register page pagination services.
     *
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(PagePaginationDataResolverInterface::class, $this->getPagePaginationResolverClosure());
        $this->app->bind(PagePaginationDataInterface::class, $this->getPagePaginationDataClosure());
    }

    /**
     * Get closure to instantiate the implementation of PagePaginationDataResolverInterface.
     *
     * @return \Closure
     */
    abstract protected function getPagePaginationResolverClosure(): Closure;

    /**
     * Create page pagination config from app config.
     *
     * @return \StepTheFkUp\Pagination\Resolvers\Config\PagePaginationConfig
     */
    protected function createPagePaginationConfig(): PagePaginationConfig
    {
        $config = $this->app->get('config')->get('pagination.page', []);

        return new PagePaginationConfig(
            $config['number_attr'] ?? static::$defaultConfig['number_attr'],
            $config['number_default'] ?? static::$defaultConfig['number_default'],
            $config['size_attr'] ?? static::$defaultConfig['size_attr'],
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
        return (new Psr7Factory())->createRequest($this->app->get('request'));
    }

    /**
     * Get closure to instantiate the page pagination data.
     *
     * @return \Closure
     */
    private function getPagePaginationDataClosure(): Closure
    {
        return function (): PagePaginationDataInterface {
            return $this->app->get(PagePaginationDataResolverInterface::class)->resolve($this->createServerRequest());
        };
    }
}