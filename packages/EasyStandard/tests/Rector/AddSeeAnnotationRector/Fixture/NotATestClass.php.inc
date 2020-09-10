<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\AddSeeAnnotationRector\Fixture;

class NotATestClass
{
    public function provideSomeData(): array
    {
        return [
            'case 1' => ['first' => 2, 'second' => 2, 'result' => 4],
        ];
    }

    /**
     * Test add.
     *
     * @param int $first
     * @param int $second
     * @param int $result
     *
     * @dataProvider provideSomeData
     *
     * @return void
     */
    public function testAdd(int $first, int $second, int $result): void
    {
        $actualResult = $first + $second;

        self::assertSame($result, $actualResult);
    }
}
