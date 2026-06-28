<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Unit\Laravel;

use EonX\EasyPipeline\Exception\EmptyPipelinesListException;
use EonX\EasyPipeline\Factory\PipelineFactoryInterface;
use EonX\EasyPipeline\Laravel\EasyPipelineServiceProvider;
use EonX\EasyPipeline\Tests\Stub\Provider\MiddlewareProviderStub;
use EonX\EasyPipeline\Tests\Unit\AbstractLaravelTestCase;

final class EasyPipelineServiceProviderTest extends AbstractLaravelTestCase
{
    public function testEmptyProvidersListException(): void
    {
        $this->expectException(EmptyPipelinesListException::class);

        $app = $this->getApplication();

        $serviceProvider = new EasyPipelineServiceProvider($app);

        $serviceProvider->boot();
        $serviceProvider->register();
    }

    public function testRegisterPipelineFactorySuccess(): void
    {
        $app = $this->getApplication();
        /** @var \Illuminate\Config\Repository $config */
        $config = \config();
        $config->set('easy-pipeline.pipelines', [
            'pipeline-1' => 'provider-1',
        ]);

        new EasyPipelineServiceProvider($app)
            ->register();

        self::assertInstanceOf(PipelineFactoryInterface::class, $app->get(PipelineFactoryInterface::class));
    }

    public function testRegisterProviderSuccess(): void
    {
        $app = $this->getApplication();
        /** @var \Illuminate\Config\Repository $config */
        $config = \config();
        $config->set('easy-pipeline.pipelines', [
            'pipeline-1' => MiddlewareProviderStub::class,
        ]);

        new EasyPipelineServiceProvider($app)
            ->register();

        self::assertInstanceOf(
            MiddlewareProviderStub::class,
            $app->get(EasyPipelineServiceProvider::PIPELINES_PREFIX . 'pipeline-1')
        );
    }
}
