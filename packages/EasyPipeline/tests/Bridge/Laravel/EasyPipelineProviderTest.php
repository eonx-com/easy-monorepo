<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Tests\Bridge\Laravel;

use StepTheFkUp\EasyPipeline\Bridge\Laravel\EasyIlluminatePipelineServiceProvider;
use StepTheFkUp\EasyPipeline\Exceptions\EmptyPipelinesListException;
use StepTheFkUp\EasyPipeline\Interfaces\PipelineFactoryInterface;
use StepTheFkUp\EasyPipeline\Tests\AbstractLumenTestCase;
use StepTheFkUp\EasyPipeline\Tests\Bridge\Laravel\Stubs\MiddlewareProviderStub;

final class EasyPipelineProviderTest extends AbstractLumenTestCase
{
    /**
     * Provider should throw exception when no repositories to register.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyPipeline\Exceptions\EmptyPipelinesListException
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
    'LoyaltyCorp\EasyPipeline\Tests\Bridge\Laravel\EasyPipelineProviderTest',
    false
);
