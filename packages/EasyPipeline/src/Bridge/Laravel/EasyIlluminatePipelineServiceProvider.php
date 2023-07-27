<?php

declare(strict_types=1);

namespace EonX\EasyPipeline\Bridge\Laravel;

use EonX\EasyPipeline\Exceptions\EmptyPipelinesListException;
use EonX\EasyPipeline\Implementations\Illuminate\IlluminatePipelineFactory;
use EonX\EasyPipeline\Interfaces\PipelineFactoryInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

final class EasyIlluminatePipelineServiceProvider extends ServiceProvider
{
    public const PIPELINES_PREFIX = 'pipeline.';

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-pipeline.php' => \base_path('config/easy-pipeline.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-pipeline.php', 'easy-pipeline');

        $this->registerPipelines();
        $this->registerPipelineFactory();
    }

    private function registerPipelineFactory(): void
    {
        $this->app->singleton(
            PipelineFactoryInterface::class,
            static fn (Container $app): IlluminatePipelineFactory => new IlluminatePipelineFactory(
                $app,
                \array_keys(\config('easy-pipeline.pipelines', [])),
                self::PIPELINES_PREFIX
            )
        );
    }

    private function registerPipelines(): void
    {
        $pipelines = (array)\config('easy-pipeline.pipelines', []);

        if (\count($pipelines) === 0) {
            throw new EmptyPipelinesListException(
                'No pipelines to register. Please make sure your application has the expected configuration'
            );
        }

        foreach ($pipelines as $pipeline => $provider) {
            $this->app->bind(self::PIPELINES_PREFIX . $pipeline, $provider);
        }
    }
}
