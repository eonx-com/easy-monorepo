<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests;

use EonX\EasyTest\Tests\Stubs\ClassToCoverStub;

final class CheckCoverTest extends AbstractTestCase
{
    public function testCoverage(): void
    {
        $stub = new ClassToCoverStub();
        $stub->setProp(1);

        self::assertEquals(1, $stub->getProp());
    }
}
