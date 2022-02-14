<?php

declare(strict_types=1);

namespace EonX\EasyRepository\Tests\Implementation\Doctrine\ORM;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use EonX\EasyPagination\Pagination;
use EonX\EasyPagination\Paginators\DoctrineOrmLengthAwarePaginatorNew;
use EonX\EasyRepository\Tests\AbstractTestCase;
use Mockery\LegacyMockInterface;

final class AbstractPaginatedDoctrineOrmRepositoryTest extends AbstractTestCase
{
    public function testPaginateSetResultsSuccessfully(): void
    {
        /** @var \Doctrine\Persistence\ManagerRegistry $registry */
        $registry = $this->mockRegistry(null, function (LegacyMockInterface $repository): void {
            $repository
                ->shouldReceive('getClassName')
                ->once()
                ->withNoArgs()
                ->andReturn('my-entity-class');
            $repository
                ->shouldReceive('createQueryBuilder')
                ->once()
                ->with('m', null)
                ->andReturn($this->mockQueryBuilderAndQuery());
        });

        $repository = new PaginatedDoctrineOrmRepositoryStub($registry, new Pagination(1, 10));
        $paginator = $repository->paginate();

        self::assertInstanceOf(DoctrineOrmLengthAwarePaginatorNew::class, $paginator);
    }

    private function mockQueryBuilderAndQuery(): LegacyMockInterface
    {
        return $this->mock(QueryBuilder::class, function (LegacyMockInterface $queryBuilder): void {
            /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
            $entityManager = $this->mock(
                EntityManagerInterface::class,
                function (LegacyMockInterface $entityManager): void {
                    $entityManager
                        ->shouldReceive('getConfiguration')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new Configuration());
                }
            );

            $queryBuilder
                ->shouldReceive('getQuery')
                ->once()
                ->withNoArgs()
                ->andReturn(new Query($entityManager));
        });
    }
}
