<?php
declare(strict_types=1);

namespace App\Tests\Quality\Ecs\ArrangeActAssertSniff\Wrong;

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
}
