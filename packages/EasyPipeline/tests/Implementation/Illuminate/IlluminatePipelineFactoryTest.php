<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Implementation\Illuminate;

use EonX\EasyPipeline\Bridge\Laravel\EasyIlluminatePipelineServiceProvider;
use EonX\EasyPipeline\Exceptions\InvalidMiddlewareProviderException;
use EonX\EasyPipeline\Exceptions\PipelineNotFoundException;
use EonX\EasyPipeline\Implementations\Illuminate\IlluminatePipelineFactory;
use EonX\EasyPipeline\Interfaces\PipelineInterface;
use EonX\EasyPipeline\Tests\AbstractLumenTestCase;
use EonX\EasyPipeline\Tests\Implementation\Illuminate\Stubs\PipelineNameAwareMiddlewareProviderStub;
use EonX\EasyPipeline\Tests\Implementation\Illuminate\Stubs\ValidMiddlewareProviderStub;
use stdClass;

final class IlluminatePipelineFactoryTest extends AbstractLumenTestCase
{
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

    public function testInvalidMiddlewareProviderForInvalidInterface(): void
    {
        $this->expectException(InvalidMiddlewareProviderException::class);

        $app = $this->getApplication();
        $app->instance('pipeline', new stdClass());

        (new IlluminatePipelineFactory($app, ['pipeline']))->create('pipeline');
    }

    public function testPipelineNameAwareMiddlewareSetsName(): void
    {
        $app = $this->getApplication();
        $app->instance('pipeline', new PipelineNameAwareMiddlewareProviderStub());

        $pipeline = (new IlluminatePipelineFactory($app, ['pipeline']))->create('pipeline');

        self::assertEquals('test-pipeline', $pipeline->process('test-'));
    }

    public function testPipelineNotFoundException(): void
    {
        $this->expectException(PipelineNotFoundException::class);

        (new IlluminatePipelineFactory($this->getApplication(), []))->create('invalid');
    }
}
