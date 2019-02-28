<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Bridge\Laravel;

use Illuminate\Support\ServiceProvider;
use StepTheFkUp\EasyPipeline\Exceptions\EmptyMiddlewareProvidersListException;
use StepTheFkUp\EasyPipeline\Implementations\Illuminate\IlluminatePipelineFactory;
use StepTheFkUp\EasyPipeline\Interfaces\PipelineFactoryInterface;

final class EasyIlluminatePipelineServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    public const PROVIDERS_PREFIX = 'middleware_provider.';

    /**
     * Register EasyPipeline services for IlluminatePipeline implementation.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register(): void
    {
        $this->registerMiddlewareProviders();

        $this->app->singleton(
            PipelineFactoryInterface::class,
            $this->getIlluminatePipelineFactoryClosure(self::PROVIDERS_PREFIX)
        );
    }

    /**
     * Get closure to instantiate the illuminate pipeline factory.
     *
     * @param string $prefix
     *
     * @return \Closure
     */
    private function getIlluminatePipelineFactoryClosure(string $prefix): \Closure
    {
        return function () use ($prefix): IlluminatePipelineFactory {
            return new IlluminatePipelineFactory($this->app, $this->app->make('config')->get('easy-pipeline.providers', []), $prefix);
        };
    }

    /**
     * Register middleware providers into the container.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function registerMiddlewareProviders(): void
    {
        $config = $this->app->make('config')->get('easy-pipeline');
        $providers = $config['providers'] ?? [];

        if (empty($providers)) {
            throw new EmptyMiddlewareProvidersListException(
                'No providers to register. Please make sure your application has the expected configuration'
            );
        }

        foreach ($providers as $pipeline => $provider) {
            $this->app->bind(self::PROVIDERS_PREFIX . $pipeline, $provider);
        }
    }
}
