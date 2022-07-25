<?php

declare(strict_types=1);

namespace EonX\EasyLock\Tests;

use EonX\EasyLock\LockData;

final class LockDataTest extends AbstractTestCase
{
    public function testUpdate(): void
    {
        $lockData = new LockData('test', 1.0, true);
        $lockData->update('test2', 2.0);

        self::assertEquals('test2', $lockData->getResource());
        self::assertEquals(2.0, $lockData->getTtl());
        self::assertTrue($lockData->shouldRetry());
    }
}
