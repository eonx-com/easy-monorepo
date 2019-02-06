<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Tests\Resolvers\Config;

use StepTheFkUp\Pagination\Resolvers\Config\PagePaginationConfig;
use StepTheFkUp\Pagination\Tests\AbstractTestCase;

final class PagePaginationConfigTest extends AbstractTestCase
{
    /**
     * PagePaginationConfig should return identical data as input.
     *
     * @return void
     */
    public function testGettersReturnIdenticalInput(): void
    {
        $config = new PagePaginationConfig('number', 10, 'size', 100);

        self::assertEquals('number', $config->getNumberAttr());
        self::assertEquals(10, $config->getNumberDefault());
        self::assertEquals('size', $config->getSizeAttr());
        self::assertEquals(100, $config->getSizeDefault());
    }
}