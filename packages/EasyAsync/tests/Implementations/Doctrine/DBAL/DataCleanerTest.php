<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Implementations\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use EonX\EasyAsync\Data\Job;
use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Exceptions\UnableToRemoveJobException;
use EonX\EasyAsync\Implementations\Doctrine\DBAL\DataCleaner;
use EonX\EasyAsync\Tests\AbstractTestCase;
use EonX\EasyAsync\Tests\Stubs\JobLogPersisterStub;
use EonX\EasyAsync\Tests\Stubs\JobPersisterStub;
use Mockery\MockInterface;

/**
 * @coversNothing
 */
final class DataCleanerTest extends AbstractTestCase
{
    public function testRemoveFailed(): void
    {
        $this->expectException(UnableToRemoveJobException::class);

        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->mock(Connection::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('beginTransaction')
                ->once()
                ->withNoArgs()
                ->andReturnTrue();

            $mock
                ->shouldReceive('rollback')
                ->once()
                ->withNoArgs()
                ->andReturnTrue();
        });

        $jobPersister = new JobPersisterStub();
        $jobLogPersister = new JobLogPersisterStub();
        $cleaner = new DataCleaner($conn, $jobLogPersister, $jobPersister);

        $cleaner->remove(new Job(new Target('id', 'type'), 'test'));
    }

    public function testRemoveSuccessfully(): void
    {
        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->mock(Connection::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('beginTransaction')
                ->once()
                ->withNoArgs()
                ->andReturnTrue();

            $mock
                ->shouldReceive('commit')
                ->once()
                ->withNoArgs()
                ->andReturnTrue();
        });

        $jobPersister = new JobPersisterStub();
        $jobLogPersister = new JobLogPersisterStub();
        $cleaner = new DataCleaner($conn, $jobLogPersister, $jobPersister);
        $job = new Job(new Target('id', 'type'), 'test');
        $job->setId('jobId');

        $cleaner->remove($job);

        self::assertCount(1, $jobPersister->getMethodCalls());
        self::assertEquals('remove', $jobPersister->getMethodCalls()[0]);
        self::assertCount(1, $jobLogPersister->getMethodCalls());
        self::assertEquals('removeForJob', $jobLogPersister->getMethodCalls()[0]);
    }
}
