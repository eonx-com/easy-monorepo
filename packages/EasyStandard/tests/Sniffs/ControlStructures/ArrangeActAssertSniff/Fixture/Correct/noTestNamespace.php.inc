<?php
declare(strict_types=1);

namespace App\NoTestNamespace;

final class TestClass
{
    public function testSomething()
    {
        $expectedResult = 4;

        $actualResult = 2 + 2;

        self::assertSame($expectedResult, $actualResult);

        echo $actualResult;
    }
}
