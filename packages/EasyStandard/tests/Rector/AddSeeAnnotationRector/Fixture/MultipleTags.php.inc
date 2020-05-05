<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\AddSeeAnnotationRector\Fixture;

use PHPUnit\Framework\TestCase;

class MultipleTags extends TestCase
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
     * Test add 1.
     *
     * @param int $first
     * @param int $second
     * @param int $result
     *
     * @dataProvider provideSomeData
     *
     * @return void
     */
    public function testAdd1(int $first, int $second, int $result): void
    {
        $actualResult = $first + $second;

        self::assertSame($result, $actualResult);
    }

    /**
     * Test add 2.
     *
     * @param int $first
     * @param int $second
     * @param int $result
     *
     * @dataProvider provideSomeData
     *
     * @return void
     */
    public function testAdd2(int $first, int $second, int $result): void
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

class MultipleTags extends TestCase
{
    /**
     * Provides data.
     *
     * @return mixed[]
     *
     * @see testAdd1
     * @see testAdd2
     */
    public function provideSomeData(): array
    {
        return [
            'case 1' => ['first' => 2, 'second' => 2, 'result' => 4],
            'case 2' => ['first' => 3, 'second' => 5, 'result' => 8],
        ];
    }

    /**
     * Test add 1.
     *
     * @param int $first
     * @param int $second
     * @param int $result
     *
     * @dataProvider provideSomeData
     *
     * @return void
     */
    public function testAdd1(int $first, int $second, int $result): void
    {
        $actualResult = $first + $second;

        self::assertSame($result, $actualResult);
    }

    /**
     * Test add 2.
     *
     * @param int $first
     * @param int $second
     * @param int $result
     *
     * @dataProvider provideSomeData
     *
     * @return void
     */
    public function testAdd2(int $first, int $second, int $result): void
    {
        $actualResult = $first + $second;

        self::assertSame($result, $actualResult);
    }
}

?>
