<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\AddSeeAnnotationRector\Fixture;

use PHPUnit\Framework\TestCase;

class TestMethodWithoutDataProvider extends TestCase
{
    /**
     * Test add.
     *
     * @return void
     */
    public function testAdd(): void
    {
        self::assertTrue(true);
    }
}
