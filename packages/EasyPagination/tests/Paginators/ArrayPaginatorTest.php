<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Paginators;

use EonX\EasyPagination\Data\StartSizeData;
use EonX\EasyPagination\Paginators\ArrayPaginator;
use EonX\EasyPagination\Tests\AbstractTestCase;

final class ArrayPaginatorTest extends AbstractTestCase
{
    public function testPaginator(): void
    {
        $paginator = new ArrayPaginator([], 10, new StartSizeData(1, 15));

        self::assertEquals([], $paginator->getItems());
        self::assertEquals(10, $paginator->getTotalItems());
    }
}
