<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPagination\Tests\Resolvers\Config;

use LoyaltyCorp\EasyPagination\Resolvers\Config\StartSizeConfig;
use LoyaltyCorp\EasyPagination\Tests\AbstractTestCase;

final class StartSizeConfigTest extends AbstractTestCase
{
    /**
     * StartSizeConfig should return identical data as input.
     *
     * @return void
     */
    public function testGettersReturnIdenticalInput(): void
    {
        $config = new StartSizeConfig('number', 10, 'size', 100);

        self::assertEquals('number', $config->getStartAttribute());
        self::assertEquals(10, $config->getStartDefault());
        self::assertEquals('size', $config->getSizeAttribute());
        self::assertEquals(100, $config->getSizeDefault());
    }
}

\class_alias(
    StartSizeConfigTest::class,
    'StepTheFkUp\EasyPagination\Tests\Resolvers\Config\StartSizeConfigTest',
    false
);
