<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\Bundle;

use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;

final class EasyDoctrineBundleTest extends AbstractUnitTestCase
{
    /**
     * Make sure we can boot the kernel correctly.
     */
    public function testItSucceeds(): void
    {
        self::bootKernel();

        self::assertTrue(true);
    }
}
