<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Unit\Pipeline;

use Closure;
use EonX\EasyPipeline\Exception\EmptyMiddlewareListException;
use EonX\EasyPipeline\Pipeline\IlluminatePipeline;
use EonX\EasyPipeline\Tests\Stub\Input\InputStub;
use EonX\EasyPipeline\Tests\Stub\Logger\LoggerChangeNameMiddlewareStub;
use EonX\EasyPipeline\Tests\Stub\Middleware\ChangeNameMiddlewareStub;
use EonX\EasyPipeline\Tests\Unit\AbstractLumenTestCase;
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
            new ChangeNameMiddlewareStub('bob'),
            new LoggerChangeNameMiddlewareStub(new ChangeNameMiddlewareStub('harry')),
            new LoggerChangeNameMiddlewareStub(new ChangeNameMiddlewareStub('brandon')),
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
            LoggerChangeNameMiddlewareStub::class => [
                'Changed name "bob" to "harry"',
                'Changed name "harry" to "brandon"',
            ],
        ], $logs);
    }
}
