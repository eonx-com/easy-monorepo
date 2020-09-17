<?php
declare(strict_types=1);

namespace App\Tests\Quality\Ecs\ArrangeActAssertSniff\Correct;

final class TestClass
{
    public function testSomething()
    {
        $value1 = 2;
        $value2 = 2;
        $expectedClosure = \array_map(static function ($value) use ($value1, $value2) {
            $result = static function () use ($value1, $value2): int {
                $result = $value1 + $value2;

                return $result + 0;
            };

            return $result();
        }, []);

        $actualResult = 2 + 2;

        self::assertSame($expectedClosure(), $actualResult);
    }
}
