<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyRepository\Tests\Implementation\Doctrine\ORM;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Mockery\MockInterface;
use LoyaltyCorp\EasyRepository\Implementations\Doctrine\ORM\LengthAwareDoctrineOrmPaginator;
use LoyaltyCorp\EasyRepository\Tests\AbstractTestCase;
use LoyaltyCorp\EasyPagination\Data\StartSizeData;

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

\class_alias(
    AbstractPaginatedDoctrineOrmRepositoryTest::class,
    'StepTheFkUp\EasyRepository\Tests\Implementation\Doctrine\ORM\AbstractPaginatedDoctrineOrmRepositoryTest',
    false
);
