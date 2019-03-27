<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Tests\Implementation\Doctrine\ORM;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Mockery\MockInterface;
use StepTheFkUp\EasyRepository\Implementations\Doctrine\ORM\LengthAwareDoctrineOrmPaginator;
use StepTheFkUp\EasyRepository\Tests\AbstractTestCase;
use StepTheFkUp\Pagination\Data\StartSizeData;

final class AbstractPaginatedDoctrineOrmRepositoryTest extends AbstractTestCase
{
    /**
     * Repository should return paginator for given query successfully.
     *
     * @return void
     */
    public function testPaginateSetResultsSuccessfully(): void
    {
        /** @var \Doctrine\Common\Persistence\ManagerRegistry $registry */
        $registry = $this->mockRegistry(null, function (MockInterface $repository): void {
            $repository
                ->shouldReceive('createQueryBuilder')
                ->once()
                ->with('m', null)
                ->andReturn($this->mockQueryBuilderAndQuery());
        });

        $repository = new PaginatedDoctrineOrmRepositoryStub($registry, new StartSizeData(1, 10));
        $paginator = $repository->paginate();

        self::assertInstanceOf(LengthAwareDoctrineOrmPaginator::class, $paginator);
    }

    /**
     * Mock queryBuilder and query.
     *
     * @return \Mockery\MockInterface
     */
    private function mockQueryBuilderAndQuery(): MockInterface
    {
        return $this->mock(QueryBuilder::class, function (MockInterface $queryBuilder): void {
            /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
            $entityManager = $this->mock(EntityManagerInterface::class, function (MockInterface $entityManager): void {
                $entityManager->shouldReceive('getConfiguration')->once()->withNoArgs()->andReturn(new Configuration());
            });

            $queryBuilder->shouldReceive('getQuery')->once()->withNoArgs()->andReturn(new Query($entityManager));
        });
    }
}
