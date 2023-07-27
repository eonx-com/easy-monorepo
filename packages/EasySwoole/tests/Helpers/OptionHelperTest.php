<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Helpers;

use EonX\EasySwoole\Helpers\OptionHelper;
use EonX\EasySwoole\Tests\AbstractTestCase;

final class OptionHelperTest extends AbstractTestCase
{
    /**
     * @see testGetBoolean
     */
    public static function providerTestGetBoolean(): iterable
    {
        yield 'True with boolean' => [
            ['test' => true],
            'test',
            true,
        ];

        yield 'True with string true' => [
            ['test' => 'true'],
            'test',
            true,
        ];

        yield 'True with string 1' => [
            ['test' => '1'],
            'test',
            true,
        ];

        yield 'True with integer 1' => [
            ['test' => 1],
            'test',
            true,
        ];

        yield 'True with integer more than 1' => [
            ['test' => 10],
            'test',
            true,
        ];

        yield 'True with small float' => [
            ['test' => 0.001],
            'test',
            true,
        ];

        yield 'False with boolean' => [
            ['test' => false],
            'test',
            false,
        ];

        yield 'False with string' => [
            ['test' => 'false'],
            'test',
            false,
        ];

        yield 'False with string 0' => [
            ['test' => '0'],
            'test',
            false,
        ];

        yield 'False with string negative' => [
            ['test' => '-1'],
            'test',
            false,
        ];

        yield 'False with integer 0' => [
            ['test' => 0],
            'test',
            false,
        ];

        yield 'False with integer negative' => [
            ['test' => -1],
            'test',
            false,
        ];

        yield 'False with small float negative' => [
            ['test' => -0.001],
            'test',
            false,
        ];

        yield 'False with non-existent key' => [
            ['test' => 'false'],
            'invalid',
            false,
        ];
    }

    /**
     * @see testIsset
     */
    public static function providerTestIsset(): iterable
    {
        yield 'Simple isset true' => [
            ['test' => 'test'],
            'test',
            true,
        ];

        yield 'Simple isset false' => [
            ['test' => 'test'],
            'invalid',
            false,
        ];
    }

    /**
     * @dataProvider providerTestGetBoolean
     */
    public function testGetBoolean(array $options, string $key, bool $expected): void
    {
        OptionHelper::setOptions($options);

        self::assertSame($expected, OptionHelper::getBoolean($key));
    }

    /**
     * @dataProvider providerTestIsset
     */
    public function testIsset(array $options, string $issetKey, bool $expected): void
    {
        OptionHelper::setOptions($options);

        self::assertSame($expected, OptionHelper::isset($issetKey));
    }
}
