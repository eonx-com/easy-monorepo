<?php

declare(strict_types=1);

namespace EonX\EasyRepository\Tests\Implementation\Doctrine\ORM;

use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use EonX\EasyRepository\Implementations\Doctrine\ORM\LengthAwareDoctrineOrmPaginator;
use EonX\EasyRepository\Tests\AbstractTestCase;
use Mockery\LegacyMockInterface;

final class LengthAwareDoctrineOrmPaginatorTest extends AbstractTestCase
{
    public function testGettersReturnExpectedValues(): void
    {
        /** @var \Doctrine\ORM\Tools\Pagination\Paginator<mixed> $doctrinePaginator */
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

    private function mockDoctrinePaginator(): LegacyMockInterface
    {
        return $this->mock(DoctrinePaginator::class, function (LegacyMockInterface $paginator): void {
            // 3 items
            $items = [new \stdClass(), new \stdClass(), new \stdClass()];
            $iterator = new \ArrayIterator($items);

            $paginator->shouldReceive('getIterator')
                ->once()
                ->withNoArgs()
                ->andReturn($iterator);
            $paginator->shouldReceive('count')
                ->once()
                ->withNoArgs()
                ->andReturn(\count($items));
        });
    }
}
