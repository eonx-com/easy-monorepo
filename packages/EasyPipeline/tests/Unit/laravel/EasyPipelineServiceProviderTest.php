<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Unit\Laravel;

use EonX\EasyPipeline\Exception\EmptyPipelinesListException;
use EonX\EasyPipeline\Factory\PipelineFactoryInterface;
use EonX\EasyPipeline\Laravel\EasyPipelineServiceProvider;
use EonX\EasyPipeline\Tests\Stub\Provider\MiddlewareProviderStub;
use EonX\EasyPipeline\Tests\Unit\AbstractLumenTestCase;

final class EasyPipelineServiceProviderTest extends AbstractLumenTestCase
{
    public function testEmptyProvidersListException(): void
    {
        $this->expectException(EmptyPipelinesListException::class);

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->getApplication();

        $serviceProvider = new EasyPipelineServiceProvider($app);

        $serviceProvider->boot();
        $serviceProvider->register();
    }

    public function testRegisterPipelineFactorySuccess(): void
    {
        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->getApplication();
        \config()
            ->set('easy-pipeline.pipelines', [
                'pipeline-1' => 'provider-1',
            ]);

        (new EasyPipelineServiceProvider($app))->register();

        self::assertInstanceOf(PipelineFactoryInterface::class, $app->get(PipelineFactoryInterface::class));
    }

    public function testRegisterProviderSuccess(): void
    {
        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->getApplication();
        \config()
            ->set('easy-pipeline.pipelines', [
                'pipeline-1' => MiddlewareProviderStub::class,
            ]);

        (new EasyPipelineServiceProvider($app))->register();

        self::assertInstanceOf(
            MiddlewareProviderStub::class,
            $app->get(EasyPipelineServiceProvider::PIPELINES_PREFIX . 'pipeline-1')
        );
    }
}
