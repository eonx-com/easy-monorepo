<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Implementations\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use EonX\EasyAsync\Data\Job;
use EonX\EasyAsync\Data\JobLog;
use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Exceptions\UnableToPersistJobLogException;
use EonX\EasyAsync\Generators\DateTimeGenerator;
use EonX\EasyAsync\Implementations\Doctrine\DBAL\JobLogPersister;
use EonX\EasyAsync\Interfaces\JobInterface;
use EonX\EasyAsync\Interfaces\JobLogInterface;
use EonX\EasyAsync\Interfaces\JobPersisterInterface;
use EonX\EasyAsync\Tests\AbstractTestCase;
use EonX\EasyAsync\Tests\Stubs\JobPersisterStub;
use EonX\EasyPagination\Data\StartSizeData;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use Mockery\MockInterface;
use Psr\Log\NullLogger;

final class JobLogPersisterTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @throws \Exception
     *
     * @see testPersistSuccessfully
     */
    public function providerPersistSuccessfully(): iterable
    {
        $now = new \DateTime();

        $jobLog = $this->newJobLog();
        $jobLog->setStartedAt($now);
        $jobLog->setStatus(JobLogInterface::STATUS_COMPLETED);

        yield 'First JobLog Success' => [
            [$jobLog],
            new Job(new Target('id', 'type'), 'test', 10),
            [
                'status' => JobInterface::STATUS_IN_PROGRESS,
                'processed' => 1,
                'failed' => 0,
                'succeeded' => 1,
                'started_at' => $now,
            ],
        ];

        $jobLog = $this->newJobLog();
        $jobLog->setStartedAt($now);
        $jobLog->setStatus(JobLogInterface::STATUS_FAILED);

        yield 'First JobLog Fail' => [
            [$jobLog],
            new Job(new Target('id', 'type'), 'test', 10),
            [
                'status' => JobInterface::STATUS_IN_PROGRESS,
                'processed' => 1,
                'failed' => 1,
                'succeeded' => 0,
                'started_at' => $now,
            ],
        ];

        $jobLog1 = $this->newJobLog();
        $jobLog1->setStartedAt($now);
        $jobLog1->setStatus(JobLogInterface::STATUS_FAILED);

        $jobLog2 = $this->newJobLog();
        $jobLog2->setStatus(JobLogInterface::STATUS_FAILED);

        $jobLog3 = $this->newJobLog();
        $jobLog3->setFinishedAt($now);
        $jobLog3->setStatus(JobLogInterface::STATUS_COMPLETED);

        yield 'Completed Failed' => [
            [$jobLog1, $jobLog2, $jobLog3],
            new Job(new Target('id', 'type'), 'test', 3),
            [
                'status' => JobInterface::STATUS_FAILED,
                'processed' => 3,
                'failed' => 2,
                'succeeded' => 1,
                'started_at' => $now,
                'finished_at' => $now,
            ],
        ];

        $jobLog4 = $this->newJobLog();
        $jobLog4->setStartedAt($now);
        $jobLog4->setStatus(JobLogInterface::STATUS_IN_PROGRESS);

        yield 'In Progress No Count Update' => [
            [$jobLog4],
            new Job(new Target('id', 'type'), 'test', 1),
            [
                'status' => JobInterface::STATUS_IN_PROGRESS,
                'processed' => 0,
                'failed' => 0,
                'succeeded' => 0,
                'started_at' => $now,
            ],
        ];
    }

    public function testFindForJob(): void
    {
        $queryBuilder = $this->mock(QueryBuilder::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('from')
                ->atLeast()
                ->once()
                ->with('job_logs', null)
                ->andReturnSelf();

            $mock
                ->shouldReceive('where')
                ->atLeast()
                ->once()
                ->with('job_id = :jobId')
                ->andReturnSelf();

            $mock
                ->shouldReceive('setParameter')
                ->atLeast()
                ->once()
                ->with('jobId', 'jobId')
                ->andReturnSelf();

            $mock
                ->shouldReceive('select')
                ->atLeast()
                ->once()
                ->withArgs(static function (string $select): bool {
                    return \in_array($select, ['COUNT(1) as _count_1', '*'], true);
                })
                ->andReturnSelf();

            $mock
                ->shouldReceive('setFirstResult')
                ->atLeast()
                ->once()
                ->with(0)
                ->andReturnSelf();

            $mock
                ->shouldReceive('setMaxResults')
                ->atLeast()
                ->once()
                ->with(15)
                ->andReturnSelf();

            $mock
                ->shouldReceive('getSQL')
                ->atLeast()
                ->once()
                ->withNoArgs()
                ->andReturn('sql query');

            $mock
                ->shouldReceive('getParameters')
                ->atLeast()
                ->once()
                ->withNoArgs()
                ->andReturn([]);

            $mock
                ->shouldReceive('getParameterTypes')
                ->atLeast()
                ->once()
                ->withNoArgs()
                ->andReturn([]);
        });

        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->mock(Connection::class, static function (MockInterface $mock) use ($queryBuilder): void {
            $mock
                ->shouldReceive('createQueryBuilder')
                ->atLeast()
                ->once()
                ->withNoArgs()
                ->andReturn($queryBuilder);

            $mock
                ->shouldReceive('fetchAssociative')
                ->atLeast()
                ->once()
                ->with('sql query', [], [])
                ->andReturn([
                    '_count_1' => 1,
                ]);

            $mock
                ->shouldReceive('fetchAllAssociative')
                ->atLeast()
                ->once()
                ->with('sql query', [])
                ->andReturn([
                    [
                        'id' => 'id',
                        'job_id' => 'jobId',
                        'type' => 'test',
                        'target_id' => 'id',
                        'target_type' => 'type',
                        'status' => JobLogInterface::STATUS_COMPLETED,
                    ],
                ]);
        });

        $persister = $this->getPersister($conn);

        $paginator = $persister->findForJob('jobId', new StartSizeData(1, 15));

        self::assertInstanceOf(LengthAwarePaginatorInterface::class, $paginator);
        self::assertCount(1, $paginator->getItems());
        self::assertEquals(1, $paginator->getTotalItems());
    }

    public function testPersistFailed(): void
    {
        $this->expectException(UnableToPersistJobLogException::class);

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

        /** @var \EonX\EasyAsync\Interfaces\JobPersisterInterface $jobPersister */
        $jobPersister = $this->mock(JobPersisterInterface::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('findOneForUpdate')
                ->once()
                ->with('jobId')
                ->andThrow(new UnableToPersistJobLogException());
        });

        $this->getPersister($conn, $jobPersister)
            ->persist(new JobLog(new Target('id', 'type'), 'test', 'jobId'));
    }

    /**
     * @param \EonX\EasyAsync\Interfaces\JobLogInterface[] $jobLogs
     * @param mixed[] $tests
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToPersistJobLogException
     * @throws \Throwable
     *
     * @dataProvider providerPersistSuccessfully
     */
    public function testPersistSuccessfully(array $jobLogs, JobInterface $job, array $tests): void
    {
        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->mock(Connection::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('beginTransaction')
                ->atLeast()
                ->once()
                ->withNoArgs()
                ->andReturnTrue();

            $mock
                ->shouldReceive('insert')
                ->atLeast()
                ->times(0)
                ->with('`job_logs`', \Mockery::type('array'));

            $mock
                ->shouldReceive('update')
                ->atLeast()
                ->times(0)
                ->with('`job_logs`', \Mockery::type('array'), \Mockery::type('array'));

            $mock
                ->shouldReceive('commit')
                ->atLeast()
                ->once()
                ->withNoArgs()
                ->andReturnTrue();
        });
        $jobPersister = (new JobPersisterStub())->setForSingle($job);
        $persister = $this->getPersister($conn, $jobPersister);

        foreach ($jobLogs as $jobLog) {
            self::assertEquals(\spl_object_hash($jobLog), \spl_object_hash($persister->persist($jobLog)));
        }

        self::assertEquals($tests['status'], $job->getStatus());
        self::assertEquals($tests['processed'], $job->getProcessed());
        self::assertEquals($tests['failed'], $job->getFailed());
        self::assertEquals($tests['succeeded'], $job->getSucceeded());
        self::assertEquals($tests['started_at'] ?? null, $job->getStartedAt());
        self::assertEquals($tests['finished_at'] ?? null, $job->getFinishedAt());
    }

    public function testPersistUpdateExistingData(): void
    {
        $jobLog = $this->newJobLog();
        $jobLog->setStartedAt(new \DateTime());
        $job = new Job(new Target('id', 'type'), 'test', 3);
        $jobPersister = (new JobPersisterStub())->setForSingle($job);

        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->mock(Connection::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('beginTransaction')
                ->atLeast()
                ->once()
                ->withNoArgs()
                ->andReturnTrue();

            $mock
                ->shouldReceive('insert')
                ->once()
                ->with('`job_logs`', \Mockery::type('array'));

            $mock
                ->shouldReceive('update')
                ->once()
                ->with('`job_logs`', \Mockery::type('array'), \Mockery::type('array'));

            $mock
                ->shouldReceive('commit')
                ->atLeast()
                ->once()
                ->withNoArgs()
                ->andReturnTrue();
        });

        $persister = $this->getPersister($conn, $jobPersister);

        $persister->persist($jobLog);
        $persister->persist($jobLog);
    }

    public function testRemoveForJob(): void
    {
        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->mock(Connection::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('executeQuery')
                ->once()
                ->with('DELETE FROM `job_logs` WHERE job_id = :jobId', [
                    'jobId' => 'jobId',
                ]);
        });

        $this->getPersister($conn)
            ->removeForJob('jobId');
    }

    private function getPersister(
        Connection $conn,
        ?JobPersisterInterface $jobPersister = null,
        ?string $table = null
    ): JobLogPersister {
        $randomGenerator = new RandomGenerator();
        $randomGenerator->setUuidV4Generator(new RamseyUuidV4Generator());

        return new JobLogPersister(
            $conn,
            new DateTimeGenerator(),
            $randomGenerator,
            $jobPersister ?? new JobPersisterStub(),
            new NullLogger(),
            $table ?? 'job_logs'
        );
    }

    private function newJobLog(): JobLogInterface
    {
        return new JobLog(new Target('id', 'type'), 'test', 'jobId');
    }
}
