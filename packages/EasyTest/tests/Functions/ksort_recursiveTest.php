<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests\Functions;

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversFunction('ksort_recursive')]
final class ksort_recursiveTest extends TestCase
{
    /**
     * @see testKsortRecursive
     */
    public static function providerTestKsortRecursive(): iterable
    {
        yield 'simple' => [
            [
                'c' => 'c',
                'b' => 'b',
                'a' => 'a',
            ],
            [
                'a' => 'a',
                'b' => 'b',
                'c' => 'c',
            ],
        ];

        yield 'nested' => [
            [
                'c' => 'c',
                'b' => [
                    'c' => 'c',
                    'b' => 'b',
                    'a' => 'a',
                ],
                'a' => 'a',
            ],
            [
                'a' => 'a',
                'b' => [
                    'a' => 'a',
                    'b' => 'b',
                    'c' => 'c',
                ],
                'c' => 'c',
            ],
        ];
    }

    #[DataProvider('providerTestKsortRecursive')]
    public function testKsortRecursive(array $expected, array $array): void
    {
        \ksort_recursive($array);

        self::assertEquals($expected, $array);
    }
}
