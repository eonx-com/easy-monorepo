<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Tests\Data;

use StepTheFkUp\Pagination\Data\PagePaginationData;
use StepTheFkUp\Pagination\Tests\AbstractTestCase;

final class PagePaginationDataTest extends AbstractTestCase
{
    /**
     * PagePaginationData should return identical data as input.
     *
     * @return void
     */
    public function testGettersReturnIdenticalInput(): void
    {
        $paginationData = new PagePaginationData(1, 10);

        self::assertEquals(1, $paginationData->getPageNumber());
        self::assertEquals(10, $paginationData->getPageSize());
    }
}
