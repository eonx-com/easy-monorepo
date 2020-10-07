<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Bridge\Symfony\Messenger;

use EonX\EasyAsync\Bridge\Symfony\Messenger\ProcessJobLogMiddleware;
use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Factories\JobLogFactory;
use EonX\EasyAsync\Generators\DateTimeGenerator;
use EonX\EasyAsync\Interfaces\JobLogInterface;
use EonX\EasyAsync\Tests\AbstractTestCase;
use EonX\EasyAsync\Tests\Stubs\JobLogPersisterStub;
use EonX\EasyAsync\Tests\Stubs\MessengerMiddlewareStub;
use EonX\EasyAsync\Tests\Stubs\WithProcessJobLogDataStub;
use EonX\EasyAsync\Updaters\JobLogUpdater;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

final class ProcessJobLogMiddlewareTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerHandle(): iterable
    {
        $withData = new WithProcessJobLogDataStub();
        $withData->setJobId('jobId');
        $withData->setTarget(new Target('id', 'type'));
        $withData->setType('test');

        yield 'Success' => [
            new Envelope($withData, [new ConsumedByWorkerStamp()]),
            static function (): void {
            },
            static function (JobLogInterface $jobLog): void {
                self::assertEquals(JobLogInterface::STATUS_COMPLETED, $jobLog->getStatus());
            },
        ];

        yield 'Failed' => [
            new Envelope($withData, [new ConsumedByWorkerStamp()]),
            static function (): void {
                throw new \Exception();
            },
            static function (JobLogInterface $jobLog): void {
                self::assertEquals(JobLogInterface::STATUS_FAILED, $jobLog->getStatus());
            },
        ];

        yield 'Skipped because envelope does not have worker stamp' => [
            new Envelope($withData),
            static function (): void {
            },
            static function ($jobLog): void {
                self::assertNull($jobLog);
            },
        ];

        yield 'Skipped because message does not implement interface' => [
            new Envelope(new \stdClass(), [new ConsumedByWorkerStamp()]),
            static function (): void {
            },
            static function ($jobLog): void {
                self::assertNull($jobLog);
            },
        ];
    }

    /**
     * @dataProvider providerHandle
     */
    public function testProcessWithJobLog(Envelope $envelope, callable $func, callable $test): void
    {
        $middleware = new ProcessJobLogMiddleware();
        $middleware->setJogLogFactory(new JobLogFactory());
        $middleware->setJobLogPersister(new JobLogPersisterStub());
        $middleware->setJobLogUpdater(new JobLogUpdater(new DateTimeGenerator()));

        $iterator = new \ArrayIterator([$middleware, new MessengerMiddlewareStub($func)]);

        $iterator->current()
            ->handle($envelope, new StackMiddleware($iterator));

        \call_user_func($test, $middleware->getJobLog());
    }
}
