<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Tests\Implementation\Doctrine\ORM;

use Closure;
use Doctrine\ORM\QueryBuilder;
use EonX\EasyRepository\Tests\AbstractTestCase;
use Mockery\MockInterface;
use stdClass;

final class AbstractDoctrineOrmRepositoryTest extends AbstractTestCase
{
    public function testAllReturnsExpectedArray(): void
    {
        $expected = [new stdClass(), new stdClass()];

        /** @var \Doctrine\Persistence\ManagerRegistry $registry */
        $registry = $this->mockRegistry(null, function (MockInterface $repo) use ($expected): void {
            $repo->shouldReceive('findAll')
                ->once()
                ->withNoArgs()
                ->andReturn($expected);
        });

        self::assertEquals($expected, (new DoctrineOrmRepositoryStub($registry))->all());
    }

    public function testCreateQueryBuilderReturnsOrmQueryBuilder(): void
    {
        /** @var \Doctrine\Persistence\ManagerRegistry $registry */
        $registry = $this->mockRegistry(null, function (MockInterface $repo): void {
            $queryBuilder = $this->mock(QueryBuilder::class);

            $repo->shouldReceive('createQueryBuilder')
                ->once()
                ->withArgs(function (string $alias, $indexBy): bool {
                    self::assertEquals('alias', $alias);
                    self::assertNull($indexBy);

                    return true;
                })->andReturn($queryBuilder);
        });

        $createQueryBuilder = $this->getMethodAsPublic(DoctrineOrmRepositoryStub::class, 'createQueryBuilder');
        $createQueryBuilder->invoke(new DoctrineOrmRepositoryStub($registry), 'alias');
    }

    public function testDeleteCallsRemoveOnObjectManager(): void
    {
        $tests = [new stdClass(), [new stdClass(), new stdClass()]];

        foreach ($tests as $test) {
            /** @var \Doctrine\Persistence\ManagerRegistry $registry */
            $registry = $this->mockRegistry($this->getManagerExpectations('remove', $test));

            (new DoctrineOrmRepositoryStub($registry))->delete($test);
        }
    }

    public function testFindReturnsExpectedValues(): void
    {
        $expected = [
            'found' => new stdClass(),
            null,
        ];

        foreach ($expected as $identifier => $object) {
            /** @var \Doctrine\Persistence\ManagerRegistry $registry */
            $registry = $this->mockRegistry(null, function (MockInterface $repo) use ($identifier, $object): void {
                $repo->shouldReceive('find')
                    ->once()
                    ->with($identifier)
                    ->andReturn($object);
            });

            self::assertEquals($object, (new DoctrineOrmRepositoryStub($registry))->find($identifier));
        }
    }

    public function testSaveCallsRemoveOnObjectManager(): void
    {
        $tests = [new stdClass(), [new stdClass(), new stdClass()]];

        foreach ($tests as $test) {
            /** @var \Doctrine\Persistence\ManagerRegistry $registry */
            $registry = $this->mockRegistry($this->getManagerExpectations('persist', $test));

            (new DoctrineOrmRepositoryStub($registry))->save($test);
        }
    }

    private function getManagerExpectations(string $method, mixed $objects): Closure
    {
        return function (MockInterface $manager) use ($method, $objects): void {
            $times = \is_array($objects) ? \count($objects) : 1;

            $manager->shouldReceive($method)
                ->times($times)
                ->withArgs(function ($object): bool {
                    self::assertInstanceOf(stdClass::class, $object);

                    return $object instanceof stdClass;
                });
            $manager->shouldReceive('flush')
                ->once()
                ->withNoArgs();
        };
    }
}
