<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Bridge\Laravel;

use Illuminate\Support\ServiceProvider;
use StepTheFkUp\EasyPipeline\Exceptions\EmptyPipelinesListException;
use StepTheFkUp\EasyPipeline\Implementations\Illuminate\IlluminatePipelineFactory;
use StepTheFkUp\EasyPipeline\Interfaces\PipelineFactoryInterface;

final class EasyIlluminatePipelineServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    public const PIPELINES_PREFIX = 'pipeline.';

    /**
     * Register EasyPipeline services for IlluminatePipeline implementation.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register(): void
    {
        $this->registerPipelines();
        $this->registerPipelineFactory();
    }

    /**
     * Register pipeline factory.
     *
     * @return void
     */
    private function registerPipelineFactory(): void
    {
        $this->app->singleton(
            PipelineFactoryInterface::class,
            function (): IlluminatePipelineFactory {
                return new IlluminatePipelineFactory(
                    $this->app,
                    \array_keys(\config('easy-pipeline.pipelines', [])),
                    self::PIPELINES_PREFIX
                );
            }
        );
    }

    /**
     * Register middleware providers into the container.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function registerPipelines(): void
    {
        $pipelines = \config('easy-pipeline.pipelines', []);

        if (empty($pipelines)) {
            throw new EmptyPipelinesListException(
                'No pipelines to register. Please make sure your application has the expected configuration'
            );
        }

        foreach ($pipelines as $pipeline => $provider) {
            $this->app->bind(self::PIPELINES_PREFIX . $pipeline, $provider);
        }
    }
}
