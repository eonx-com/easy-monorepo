<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPipeline\Tests\Bridge\Laravel;

use LoyaltyCorp\EasyPipeline\Bridge\Laravel\EasyIlluminatePipelineServiceProvider;
use LoyaltyCorp\EasyPipeline\Exceptions\EmptyPipelinesListException;
use LoyaltyCorp\EasyPipeline\Interfaces\PipelineFactoryInterface;
use LoyaltyCorp\EasyPipeline\Tests\AbstractLumenTestCase;
use LoyaltyCorp\EasyPipeline\Tests\Bridge\Laravel\Stubs\MiddlewareProviderStub;

final class EasyPipelineProviderTest extends AbstractLumenTestCase
{
    /**
     * Provider should throw exception when no repositories to register.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyPipeline\Exceptions\EmptyPipelinesListException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testEmptyProvidersListException(): void
    {
        $this->expectException(EmptyPipelinesListException::class);

        $app = $this->getApplication();

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $serviceProvider = new EasyIlluminatePipelineServiceProvider($app);

        $serviceProvider->boot();
        $serviceProvider->register();
    }

    /**
     * Provider should register the pipeline factory into the container.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testRegisterPipelineFactorySuccess(): void
    {
        $app = $this->getApplication();
        \config()->set('easy-pipeline.pipelines', ['pipeline-1' => 'provider-1']);

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        (new EasyIlluminatePipelineServiceProvider($app))->register();

        self::assertInstanceOf(PipelineFactoryInterface::class, $app->get(PipelineFactoryInterface::class));
    }

    /**
     * Provider should register middleware providers for pipelines and prefix them to avoid names clashes.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testRegisterProviderSuccess(): void
    {
        $app = $this->getApplication();
        \config()->set('easy-pipeline.pipelines', ['pipeline-1' => MiddlewareProviderStub::class]);

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        (new EasyIlluminatePipelineServiceProvider($app))->register();

        self::assertInstanceOf(
            MiddlewareProviderStub::class,
            $app->get(EasyIlluminatePipelineServiceProvider::PIPELINES_PREFIX . 'pipeline-1')
        );
    }
}

\class_alias(
    EasyPipelineProviderTest::class,
    'StepTheFkUp\EasyPipeline\Tests\Bridge\Laravel\EasyPipelineProviderTest',
    false
);
