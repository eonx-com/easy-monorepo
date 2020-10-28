<?php
declare(strict_types=1);

namespace App\Tests\Quality\Ecs\ArrangeActAssertSniff\Correct;

final class TestClass
{
    public function noTestMethod()
    {
        $expectedResult = 4;
        $actualResult = 2 + 2;
        self::assertSame($expectedResult, $actualResult);
    }
}
