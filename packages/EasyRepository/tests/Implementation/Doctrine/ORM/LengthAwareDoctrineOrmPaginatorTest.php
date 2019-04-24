<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyRepository\Tests\Implementation\Doctrine\ORM;

use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use LoyaltyCorp\EasyRepository\Implementations\Doctrine\ORM\LengthAwareDoctrineOrmPaginator;
use LoyaltyCorp\EasyRepository\Tests\AbstractTestCase;
use Mockery\MockInterface;

final class LengthAwareDoctrineOrmPaginatorTest extends AbstractTestCase
{
    /**
     * Paginator should return expected values from getters.
     *
     * @return void
     */
    public function testGettersReturnExpectedValues(): void
    {
        /** @var \Doctrine\ORM\Tools\Pagination\Paginator $doctrinePaginator */
        $doctrinePaginator = $this->mockDoctrinePaginator();
        $paginator = new LengthAwareDoctrineOrmPaginator($doctrinePaginator, 1, 2);

        self::assertCount(3, $paginator->getItems());
        self::assertEquals(3, $paginator->getTotalItems());
        self::assertEquals(1, $paginator->getCurrentPage());
        self::assertEquals(2, $paginator->getTotalPages());
        self::assertEquals(2, $paginator->getItemsPerPage());
        self::assertTrue($paginator->hasNextPage());
        self::assertFalse($paginator->hasPreviousPage());
    }

    /**
     * Mock doctrine paginator.
     *
     * @return \Mockery\MockInterface
     */
    private function mockDoctrinePaginator(): MockInterface
    {
        return $this->mock(DoctrinePaginator::class, function (MockInterface $paginator): void {
            $items = [new \stdClass(), new \stdClass(), new \stdClass()]; // 3 items
            $iterator = new \ArrayIterator($items);

            $paginator->shouldReceive('getIterator')->once()->withNoArgs()->andReturn($iterator);
            $paginator->shouldReceive('count')->once()->withNoArgs()->andReturn(\count($items));
        });
    }
}

\class_alias(
    LengthAwareDoctrineOrmPaginatorTest::class,
    'StepTheFkUp\EasyRepository\Tests\Implementation\Doctrine\ORM\LengthAwareDoctrineOrmPaginatorTest',
    false
);
