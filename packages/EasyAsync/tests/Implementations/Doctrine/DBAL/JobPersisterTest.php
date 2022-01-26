<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Implementations\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use EonX\EasyAsync\Data\Job;
use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Exceptions\UnableToFindJobException;
use EonX\EasyAsync\Exceptions\UnableToPersistJobException;
use EonX\EasyAsync\Generators\DateTimeGenerator;
use EonX\EasyAsync\Implementations\Doctrine\DBAL\JobPersister;
use EonX\EasyAsync\Interfaces\JobInterface;
use EonX\EasyAsync\Tests\AbstractTestCase;
use EonX\EasyPagination\Data\StartSizeData;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use Mockery\MockInterface;

final class JobPersisterTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testListMethods
     */
    public function providerListMethods(): iterable
    {
        yield 'findForTarget' => [
            static function (JobPersister $persister): LengthAwarePaginatorInterface {
                return $persister->findForTarget(new Target('id', 'type'), new StartSizeData(1, 15));
            },
            static function (MockInterface $mock): void {
                $mock
                    ->shouldReceive('where')
                    ->atLeast()
                    ->once()
                    ->with('target_type = :targetType')
                    ->andReturnSelf();

                $mock
                    ->shouldReceive('andWhere')
                    ->atLeast()
                    ->once()
                    ->with('target_id = :targetId')
                    ->andReturnSelf();

                $mock
                    ->shouldReceive('setParameters')
                    ->atLeast()
                    ->once()
                    ->with([
                        'targetType' => 'type',
                        'targetId' => 'id',
                    ])
                    ->andReturnSelf();
            },
        ];

        yield 'findForTargetType' => [
            static function (JobPersister $persister): LengthAwarePaginatorInterface {
                return $persister->findForTargetType(new Target('id', 'type'), new StartSizeData(1, 15));
            },
            static function (MockInterface $mock): void {
                $mock
                    ->shouldReceive('where')
                    ->atLeast()
                    ->once()
                    ->with('target_type = :targetType')
                    ->andReturnSelf();

                $mock
                    ->shouldReceive('setParameter')
                    ->atLeast()
                    ->once()
                    ->with('targetType', 'type')
                    ->andReturnSelf();
            },
        ];

        yield 'findForType' => [
            static function (JobPersister $persister): LengthAwarePaginatorInterface {
                return $persister->findForType('test', new StartSizeData(1, 15));
            },
            static function (MockInterface $mock): void {
                $mock
                    ->shouldReceive('where')
                    ->atLeast()
                    ->once()
                    ->with('type = :type')
                    ->andReturnSelf();

                $mock
                    ->shouldReceive('setParameter')
                    ->atLeast()
                    ->once()
                    ->with('type', 'test')
                    ->andReturnSelf();
            },
        ];
    }

    public function testFindForUpdateSuccessfully(): void
    {
        $this->doTestFindJob(true);
    }

    public function testFindSuccessfully(): void
    {
        $this->doTestFindJob();
    }

    public function testFindThrowsExceptionForConnectionException(): void
    {
        $this->expectException(UnableToFindJobException::class);

        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->mock(Connection::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('fetchAssoc')
                ->once()
                ->with('SELECT * FROM `jobs` WHERE id = :jobId', [
                    'jobId' => 'jobId',
                ])
                ->andThrow(new \Exception());
        });

        $this->getPersister($conn)
            ->find('jobId');
    }

    public function testFindThrowsExceptionForNonArray(): void
    {
        $this->expectException(UnableToFindJobException::class);

        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->mock(Connection::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('fetchAssoc')
                ->once()
                ->with('SELECT * FROM `jobs` WHERE id = :jobId', [
                    'jobId' => 'jobId',
                ])
                ->andReturn(false);
        });

        $this->getPersister($conn)
            ->find('jobId');
    }

    /**
     * @dataProvider providerListMethods
     */
    public function testListMethods(callable $callMethod, callable $queryBuilderExpectations): void
    {
        $queryBuilder = $this->mock(QueryBuilder::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('from')
                ->atLeast()
                ->once()
                ->with('jobs', null)
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

        \call_user_func($queryBuilderExpectations, $queryBuilder);

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
                        'type' => 'test',
                        'target_id' => 'id',
                        'target_type' => 'type',
                        'status' => JobInterface::STATUS_SCHEDULED,
                        'total' => 1,
                        'processed' => 0,
                        'failed' => 0,
                        'succeeded' => 0,
                    ],
                ]);
        });

        /** @var \EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface $paginator */
        $paginator = \call_user_func($callMethod, $this->getPersister($conn));

        self::assertInstanceOf(LengthAwarePaginatorInterface::class, $paginator);
        self::assertCount(1, $paginator->getItems());
        self::assertEquals(1, $paginator->getTotalItems());
        self::assertInstanceOf(JobInterface::class, $paginator->getItems()[0]);
    }

    public function testPersistSuccessfully(): void
    {
        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->mock(Connection::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('insert')
                ->once()
                ->with('`jobs`', \Mockery::type('array'))
                ->andReturn(true);
        });

        $job = new Job(new Target('id', 'type'), 'test');

        $this->getPersister($conn)
            ->persist($job);

        self::assertNotNull($job->getId());
    }

    public function testPersistThrowException(): void
    {
        $this->expectException(UnableToPersistJobException::class);

        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->mock(Connection::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('insert')
                ->once()
                ->with('`jobs`', \Mockery::type('array'))
                ->andThrow(new \Exception());
        });

        $this->getPersister($conn)
            ->persist(new Job(new Target('id', 'type'), 'test'));
    }

    public function testRemoveSuccessfully(): void
    {
        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->mock(Connection::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('executeQuery')
                ->once()
                ->with('DELETE FROM `jobs` WHERE id = :jobId', [
                    'jobId' => 'jobId',
                ])
                ->andReturn(true);
        });

        $job = new Job(new Target('id', 'type'), 'test');
        $job->setId('jobId');

        $this->getPersister($conn)
            ->remove($job);
    }

    private function doTestFindJob(?bool $forUpdate = null): void
    {
        $forUpdate = $forUpdate ?? false;

        $expected = [
            'id' => 'id',
            'target_id' => 'id',
            'target_type' => 'type',
            'total' => 1,
            'processed' => 0,
            'failed' => 0,
            'succeeded' => 0,
            'type' => 'test',
            'status' => JobInterface::STATUS_SCHEDULED,
        ];

        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->mock(
            Connection::class,
            static function (MockInterface $mock) use ($forUpdate, $expected): void {
                $sql = 'SELECT * FROM `jobs` WHERE id = :jobId';

                if ($forUpdate) {
                    $sql .= ' FOR UPDATE';
                }

                $mock
                    ->shouldReceive('fetchAssoc')
                    ->once()
                    ->with($sql, [
                        'jobId' => 'jobId',
                    ])
                    ->andReturn($expected);
            }
        );

        $method = $forUpdate ? 'findOneForUpdate' : 'find';

        /** @var \EonX\EasyAsync\Interfaces\JobInterface $job */
        $job = $this->getPersister($conn)
            ->{$method}('jobId');

        self::assertEquals($expected['id'], $job->getId());
        self::assertEquals($expected['target_id'], $job->getTargetId());
        self::assertEquals($expected['target_type'], $job->getTargetType());
        self::assertEquals($expected['total'], $job->getTotal());
        self::assertEquals($expected['processed'], $job->getProcessed());
        self::assertEquals($expected['failed'], $job->getFailed());
        self::assertEquals($expected['succeeded'], $job->getSucceeded());
        self::assertEquals($expected['status'], $job->getStatus());
        self::assertEquals($expected['type'], $job->getType());
    }

    private function getPersister(Connection $conn, ?string $table = null): JobPersister
    {
        $randomGenerator = new RandomGenerator();
        $randomGenerator->setUuidV4Generator(new RamseyUuidV4Generator());

        return new JobPersister($conn, new DateTimeGenerator(), $randomGenerator, $table ?? 'jobs');
    }
}
