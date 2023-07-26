<?php

declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Implementation\Illuminate;

use Closure;
use EonX\EasyPipeline\Exceptions\EmptyMiddlewareListException;
use EonX\EasyPipeline\Implementations\Illuminate\IlluminatePipeline;
use EonX\EasyPipeline\Tests\AbstractLumenTestCase;
use EonX\EasyPipeline\Tests\Implementation\Illuminate\Stubs\ChangeNameMiddleware;
use EonX\EasyPipeline\Tests\Implementation\Illuminate\Stubs\InputStub;
use EonX\EasyPipeline\Tests\Implementation\Illuminate\Stubs\LoggerChangeNameMiddleware;
use Illuminate\Pipeline\Pipeline;

final class IlluminatePipelineTest extends AbstractLumenTestCase
{
    public function actAsMiddleware(mixed $input, Closure $next): mixed
    {
        // Just pass input to next
        return $next($input);
    }

    public function testEmptyMiddlewareListException(): void
    {
        $this->expectException(EmptyMiddlewareListException::class);

        new IlluminatePipeline(new Pipeline(), []);
    }

    public function testProcessInputThroughMiddlewareListSuccessfullyWithLogs(): void
    {
        $middlewareList = [
            new ChangeNameMiddleware('bob'),
            new LoggerChangeNameMiddleware(new ChangeNameMiddleware('harry')),
            new LoggerChangeNameMiddleware(new ChangeNameMiddleware('brandon')),
            function ($input, $next) {
                if ($input instanceof InputStub) {
                    $input->setName('nathan');
                }

                return $next($input);
            },
            $this->actAsMiddleware(...),
        ];

        $pipeline = new IlluminatePipeline(new Pipeline(), $middlewareList);
        $input = new InputStub('matt');

        $pipeline->process($input);

        $this->assertNameAndLogs($input->getName(), $pipeline->getLogs());

        $pipeline->process($input);

        $this->assertNameAndLogs($input->getName(), $pipeline->getLogs());
    }

    /**
     * @param string[] $logs
     */
    private function assertNameAndLogs(string $name, array $logs): void
    {
        self::assertEquals('nathan', $name);
        self::assertEquals([
            LoggerChangeNameMiddleware::class => [
                'Changed name "bob" to "harry"',
                'Changed name "harry" to "brandon"',
            ],
        ], $logs);
    }
}
