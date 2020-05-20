<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\AddSeeAnnotationRector\Fixture;

use PHPUnit\Framework\TestCase;

class MissingTag extends TestCase
{
    /**
     * Provides data.
     *
     * @return mixed[]
     */
    public function provideSomeData(): array
    {
        return [
            'case 1' => ['first' => 2, 'second' => 2, 'result' => 4],
            'case 2' => ['first' => 3, 'second' => 5, 'result' => 8],
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

?>
-----
<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\AddSeeAnnotationRector\Fixture;

use PHPUnit\Framework\TestCase;

class MissingTag extends TestCase
{
    /**
     * Provides data.
     *
     * @return mixed[]
     *
     * @see testAdd
     */
    public function provideSomeData(): array
    {
        return [
            'case 1' => ['first' => 2, 'second' => 2, 'result' => 4],
            'case 2' => ['first' => 3, 'second' => 5, 'result' => 8],
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

?>
