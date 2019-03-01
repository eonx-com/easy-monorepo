<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Tests\Bridge\Laravel;

use StepTheFkUp\EasyPipeline\Bridge\Laravel\EasyIlluminatePipelineServiceProvider;
use StepTheFkUp\EasyPipeline\Exceptions\EmptyMiddlewareProvidersListException;
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
     * @throws \StepTheFkUp\EasyPipeline\Exceptions\EmptyMiddlewareProvidersListException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testEmptyProvidersListException(): void
    {
        $this->expectException(EmptyMiddlewareProvidersListException::class);

        $app = $this->getApplication();

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        (new EasyIlluminatePipelineServiceProvider($app))->register();
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
        \config()->set('easy-pipeline.providers', ['pipeline-1' => 'provider-1']);

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
        \config()->set('easy-pipeline.providers', ['pipeline-1' => MiddlewareProviderStub::class]);

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        (new EasyIlluminatePipelineServiceProvider($app))->register();

        self::assertInstanceOf(
            MiddlewareProviderStub::class,
            $app->get(EasyIlluminatePipelineServiceProvider::PROVIDERS_PREFIX . 'pipeline-1')
        );
    }
}