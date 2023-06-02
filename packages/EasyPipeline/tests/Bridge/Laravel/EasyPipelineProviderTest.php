<?php

declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Bridge\Laravel;

use EonX\EasyPipeline\Bridge\Laravel\EasyIlluminatePipelineServiceProvider;
use EonX\EasyPipeline\Exceptions\EmptyPipelinesListException;
use EonX\EasyPipeline\Interfaces\PipelineFactoryInterface;
use EonX\EasyPipeline\Tests\AbstractLumenTestCase;
use EonX\EasyPipeline\Tests\Bridge\Laravel\Stubs\MiddlewareProviderStub;

final class EasyPipelineProviderTest extends AbstractLumenTestCase
{
    public function testEmptyProvidersListException(): void
    {
        $this->expectException(EmptyPipelinesListException::class);

        $app = $this->getApplication();

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $serviceProvider = new EasyIlluminatePipelineServiceProvider($app);

        $serviceProvider->boot();
        $serviceProvider->register();
    }

    public function testRegisterPipelineFactorySuccess(): void
    {
        $app = $this->getApplication();
        \config()
            ->set('easy-pipeline.pipelines', [
                'pipeline-1' => 'provider-1',
            ]);

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        (new EasyIlluminatePipelineServiceProvider($app))->register();

        self::assertInstanceOf(PipelineFactoryInterface::class, $app->get(PipelineFactoryInterface::class));
    }

    public function testRegisterProviderSuccess(): void
    {
        $app = $this->getApplication();
        \config()
            ->set('easy-pipeline.pipelines', [
                'pipeline-1' => MiddlewareProviderStub::class,
            ]);

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        (new EasyIlluminatePipelineServiceProvider($app))->register();

        self::assertInstanceOf(
            MiddlewareProviderStub::class,
            $app->get(EasyIlluminatePipelineServiceProvider::PIPELINES_PREFIX . 'pipeline-1'),
        );
    }
}
