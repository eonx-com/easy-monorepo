<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Tests\Data;

use StepTheFkUp\Pagination\Data\StartSizeData;
use StepTheFkUp\Pagination\Tests\AbstractTestCase;

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
