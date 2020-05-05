<?php
declare(strict_types=1);

namespace App\Tests\Quality\Ecs\ArrangeActAssertSniff\Correct;

final class TestClass
{
    public function testSomething()
    {
        $expectedResult = 4;
        $array = [
            'key' => 'value',
        ];

        $actualResult = 2 + 2;

        self::assertSame($expectedResult, $actualResult);
        self::assertSame(['key' => 'value'], $array);
    }

    public function testSomethingElse()
    {
        $actualResult = 2 + 2;

        self::assertSame(4, $actualResult);
    }
}
