<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Paginators;

use EonX\EasyPagination\Data\StartSizeData;
use EonX\EasyPagination\Paginators\EmptyPaginator;
use EonX\EasyPagination\Tests\AbstractTestCase;

final class EmptyPaginatorTest extends AbstractTestCase
{
    public function testPaginator(): void
    {
        $paginator = new EmptyPaginator(new StartSizeData(1, 15));

        self::assertEquals([], $paginator->getItems());
        self::assertEquals(0, $paginator->getTotalItems());
    }
}
