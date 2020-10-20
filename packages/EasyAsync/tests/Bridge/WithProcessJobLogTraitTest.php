<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Bridge;

use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Factories\JobLogFactory;
use EonX\EasyAsync\Generators\DateTimeGenerator;
use EonX\EasyAsync\Interfaces\JobLogInterface;
use EonX\EasyAsync\Tests\AbstractTestCase;
use EonX\EasyAsync\Tests\Stubs\JobLogPersisterStub;
use EonX\EasyAsync\Tests\Stubs\WithProcessJobLogDataStub;
use EonX\EasyAsync\Tests\Stubs\WithProcessJobLogTraitStub;
use EonX\EasyAsync\Updaters\JobLogUpdater;

/**
 * @coversNothing
 */
final class WithProcessJobLogTraitTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testProcessWithJobLog
     */
    public function providerProcessWithJobLog(): iterable
    {
        yield 'Success' => [
            static function (): void {
            },
            static function (JobLogInterface $jobLog): void {
                self::assertEquals(JobLogInterface::STATUS_COMPLETED, $jobLog->getStatus());
            },
        ];

        yield 'Failed' => [
            static function (): void {
                throw new \Exception();
            },
            static function (JobLogInterface $jobLog): void {
                self::assertEquals(JobLogInterface::STATUS_FAILED, $jobLog->getStatus());
            },
        ];
    }

    /**
     * @dataProvider providerProcessWithJobLog
     */
    public function testProcessWithJobLog(callable $func, callable $test): void
    {
        $withData = new WithProcessJobLogDataStub();
        $withData->setJobId('jobId');
        $withData->setTarget(new Target('id', 'type'));
        $withData->setType('test');

        $stub = new WithProcessJobLogTraitStub();
        $stub->setJogLogFactory(new JobLogFactory());
        $stub->setJobLogPersister(new JobLogPersisterStub());
        $stub->setJobLogUpdater(new JobLogUpdater(new DateTimeGenerator()));

        $stub->process($withData, $func);

        \call_user_func($test, $stub->getJobLog());
    }
}
