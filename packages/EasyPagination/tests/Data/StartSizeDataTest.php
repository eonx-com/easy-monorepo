<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPagination\Tests\Data;

use LoyaltyCorp\EasyPagination\Data\StartSizeData;
use LoyaltyCorp\EasyPagination\Tests\AbstractTestCase;

final class StartSizeDataTest extends AbstractTestCase
{
    /**
     * StartSizeData should return identical data as input.
     *
     * @return void
     */
    public function testGettersReturnIdenticalInput(): void
    {
        $paginationData = new StartSizeData(1, 10);

        self::assertEquals(1, $paginationData->getStart());
        self::assertEquals(10, $paginationData->getSize());
    }
}

\class_alias(
    StartSizeDataTest::class,
    'StepTheFkUp\EasyPagination\Tests\Data\StartSizeDataTest',
    false
);
