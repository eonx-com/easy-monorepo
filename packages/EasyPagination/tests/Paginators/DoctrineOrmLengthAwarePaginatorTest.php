<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Paginators;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EonX\EasyPagination\Data\StartSizeData;
use EonX\EasyPagination\Paginators\DoctrineOrmLengthAwarePaginator;
use EonX\EasyPagination\Tests\AbstractWithMockTestCase;
use Mockery\MockInterface;

final class DoctrineOrmLengthAwarePaginatorTest extends AbstractWithMockTestCase
{
    public function testGetTotalItems(): void
    {
        /** @var \Doctrine\ORM\EntityManagerInterface $manager */
        $manager = $this->mock(EntityManagerInterface::class, function (MockInterface $mock): void {
            $queryBuilder = $this->mock(QueryBuilder::class, function (MockInterface $mock): void {
                $mock->shouldReceive('select')
                    ->once()
                    ->with('COUNT(DISTINCT t) as _count_t')
                    ->andReturnSelf();

                $mock->shouldReceive('from')
                    ->once()
                    ->with('table', 't')
                    ->andReturnSelf();

                $query = $this->mock(AbstractQuery::class, function (MockInterface $mock): void {
                    $mock->shouldReceive('getResult')->once()->withNoArgs()->andReturn([['_count_t' => 3]]);
                });

                $mock->shouldReceive('getQuery')->once()->andReturn($query);
            });

            $mock->shouldReceive('createQueryBuilder')->once()->withNoArgs()->andReturn($queryBuilder);
        });

        $paginator = new DoctrineOrmLengthAwarePaginator($manager, new StartSizeData(1, 15), 'table', 't');

        self::assertEquals(3, $paginator->getTotalItems());
    }
}
