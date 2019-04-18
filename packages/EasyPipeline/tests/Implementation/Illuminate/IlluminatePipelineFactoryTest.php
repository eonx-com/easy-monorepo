<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Tests\Implementation\Illuminate;

use StepTheFkUp\EasyPipeline\Bridge\Laravel\EasyIlluminatePipelineServiceProvider;
use StepTheFkUp\EasyPipeline\Exceptions\InvalidMiddlewareProviderException;
use StepTheFkUp\EasyPipeline\Exceptions\PipelineNotFoundException;
use StepTheFkUp\EasyPipeline\Implementations\Illuminate\IlluminatePipelineFactory;
use StepTheFkUp\EasyPipeline\Interfaces\PipelineInterface;
use StepTheFkUp\EasyPipeline\Tests\AbstractLumenTestCase;
use StepTheFkUp\EasyPipeline\Tests\Implementation\Illuminate\Stubs\PipelineNameAwareMiddlewareProviderStub;
use StepTheFkUp\EasyPipeline\Tests\Implementation\Illuminate\Stubs\ValidMiddlewareProviderStub;

final class IlluminatePipelineFactoryTest extends AbstractLumenTestCase
{
    /**
     * Factory should create pipeline successfully and cache it to return it if asked again.
     *
     * @return void
     */
    public function testCreatePipelineSuccessfullyWithPrefixAndCacheResolved(): void
    {
        $prefix = EasyIlluminatePipelineServiceProvider::PIPELINES_PREFIX;

        $app = $this->getApplication();
        $app->instance($prefix . 'pipeline', new ValidMiddlewareProviderStub());

        $factory = new IlluminatePipelineFactory($app, ['pipeline'], $prefix);
        $pipeline = $factory->create('pipeline');

        self::assertInstanceOf(PipelineInterface::class, $pipeline);
        self::assertEquals(\spl_object_hash($pipeline), \spl_object_hash($factory->create('pipeline')));
    }

    /**
     * Factory should throw an exception if resolved middleware provider doesn't implement the expected interface.
     *
     * @return void
     */
    public function testInvalidMiddlewareProviderForInvalidInterface(): void
    {
        $this->expectException(InvalidMiddlewareProviderException::class);

        $app = $this->getApplication();
        $app->instance('pipeline', new \stdClass());

        (new IlluminatePipelineFactory($app, ['pipeline']))->create('pipeline');
    }

    /**
     * Factory should set the name of the pipeline on PipelineNameAware providers.
     *
     * @return void
     */
    public function testPipelineNameAwareMiddlewareSetsName(): void
    {
        $app = $this->getApplication();
        $app->instance('pipeline', new PipelineNameAwareMiddlewareProviderStub());

        $pipeline = (new IlluminatePipelineFactory($app, ['pipeline']))->create('pipeline');

        self::assertEquals('test-pipeline', $pipeline->process('test-'));
    }

    /**
     * Factory should throw an exception if given pipeline isn't set in mapping.
     *
     * @return void
     */
    public function testPipelineNotFoundException(): void
    {
        $this->expectException(PipelineNotFoundException::class);

        (new IlluminatePipelineFactory($this->getApplication(), []))->create('invalid');
    }
}

\class_alias(
    IlluminatePipelineFactoryTest::class,
    'LoyaltyCorp\EasyPipeline\Tests\Implementation\Illuminate\IlluminatePipelineFactoryTest',
    false
);
