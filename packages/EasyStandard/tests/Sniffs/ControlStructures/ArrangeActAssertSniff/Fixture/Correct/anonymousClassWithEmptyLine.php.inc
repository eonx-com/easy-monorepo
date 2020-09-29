<?php
declare(strict_types=1);

namespace App\Tests\Quality\Ecs\ArrangeActAssertSniff\Correct;

final class TestClass
{
    public function testSomething()
    {
        $object = new class() {
            public function getResult(): int
            {
                $value = 1;

                return $value + 0;
            }
        };

        $result = $object->getResult();

        self::assertSame(1, $result);
    }
}
