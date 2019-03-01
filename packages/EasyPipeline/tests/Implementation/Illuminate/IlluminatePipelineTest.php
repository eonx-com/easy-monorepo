<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Tests\Implementation\Illuminate;

use Illuminate\Pipeline\Pipeline;
use StepTheFkUp\EasyPipeline\Exceptions\EmptyMiddlewareListException;
use StepTheFkUp\EasyPipeline\Implementations\Illuminate\IlluminatePipeline;
use StepTheFkUp\EasyPipeline\Tests\AbstractLumenTestCase;
use StepTheFkUp\EasyPipeline\Tests\Implementation\Illuminate\Stubs\ChangeNameMiddleware;
use StepTheFkUp\EasyPipeline\Tests\Implementation\Illuminate\Stubs\InputStub;
use StepTheFkUp\EasyPipeline\Tests\Implementation\Illuminate\Stubs\LoggerChangeNameMiddleware;

final class IlluminatePipelineTest extends AbstractLumenTestCase
{
    /**
     * For callable in pipeline tests purposes.
     *
     * @param mixed $input
     * @param \Closure $next
     *
     * @return mixed
     */
    public function actAsMiddleware($input, \Closure $next)
    {
        // Just pass input to next
        return $next($input);
    }

    /**
     * Pipeline should throw an exception when given middleware list is empty.
     *
     * @return void
     */
    public function testEmptyMiddlewareListException(): void
    {
        $this->expectException(EmptyMiddlewareListException::class);

        new IlluminatePipeline(new Pipeline(), []);
    }

    /**
     * Pipeline should process input through given middleware list and be able to process multiple times.
     *
     * @return void
     */
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
            [$this, 'actAsMiddleware']
        ];

        $pipeline = new IlluminatePipeline(new Pipeline(), $middlewareList);
        $input = new InputStub('matt');

        $pipeline->process($input);

        $this->assertNameAndLogs($input->getName(), $pipeline->getLogs());

        $pipeline->process($input);

        $this->assertNameAndLogs($input->getName(), $pipeline->getLogs());
    }

    /**
     * Assert given name and logs for the success pipeline case.
     *
     * @param string $name
     * @param string[] $logs
     *
     * @return void
     */
    private function assertNameAndLogs(string $name, array $logs): void
    {
        self::assertEquals('nathan', $name);
        self::assertEquals([
            LoggerChangeNameMiddleware::class => [
                'Changed name "bob" to "harry"',
                'Changed name "harry" to "brandon"'
            ]
        ], $logs);
    }
}
