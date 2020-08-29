<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Helpers;

use EonX\EasyCore\Helpers\TrimStringsRecursive;
use EonX\EasyCore\Tests\AbstractTestCase;

/**
 * @covers \EonX\EasyCore\Helpers\TrimStringsRecursive
 */
final class TrimStringsRecursiveTest extends AbstractTestCase
{
    /**
     * @return mixed[]
     *
     * @see testCleanSucceeds
     */
    public function provideDataForClean(): array
    {
        return [
            'tests clean if data is a string' => [
                'data' => '  123  ',
                'except' => [],
                'expectedResult' => '123',
            ],
            'tests clean if data is an integer' => [
                'data' => 123,
                'except' => [],
                'expectedResult' => 123,
            ],
            'tests clean if data is an array' => [
                'data' => ['  123  ', '  abc  '],
                'except' => [],
                'expectedResult' => ['123', 'abc'],
            ],
            'tests clean if data is an assoc array' => [
                'data' => ['key1' => '  123  ', 'key2' => '  abc  '],
                'except' => [],
                'expectedResult' => ['key1' => '123', 'key2' => 'abc'],
            ],
            'tests clean if data is an assoc array (except key1)' => [
                'data' => ['key1' => '  123  ', 'key2' => '  abc  '],
                'except' => ['key1'],
                'expectedResult' => ['key1' => '  123  ', 'key2' => 'abc'],
            ],
            'tests clean if data is a multidimensional assoc array' => [
                'data' => [
                    'key1' => '  123  ',
                    'key2' => '  aBc  ',
                    'key3' => [
                        'key3.1' => '  456  ',
                        'key3.2' => '  dEf  ',
                        'key3.3' => [
                            'key3.3.1' => '  789  ',
                            'key3.3.2' => '  gHi  ',
                        ],
                    ],
                ],
                'except' => [],
                'expectedResult' => [
                    'key1' => '123',
                    'key2' => 'aBc',
                    'key3' => [
                        'key3.1' => '456',
                        'key3.2' => 'dEf',
                        'key3.3' => [
                            'key3.3.1' => '789',
                            'key3.3.2' => 'gHi',
                        ],
                    ],
                ],
            ],
            'tests clean if data is a multidimensional assoc array (except key3.key3.3.key3.3.2)' => [
                'data' => [
                    'key1' => '  123  ',
                    'key2' => '  aBc  ',
                    'key3' => [
                        'key3.1' => '  456  ',
                        'key3.2' => '  dEf  ',
                        'key3.3' => [
                            'key3.3.1' => '  789  ',
                            'key3.3.2' => '  gHi  ',
                        ],
                    ],
                ],
                'except' => ['key3.key3.3.key3.3.2'],
                'expectedResult' => [
                    'key1' => '123',
                    'key2' => 'aBc',
                    'key3' => [
                        'key3.1' => '456',
                        'key3.2' => 'dEf',
                        'key3.3' => [
                            'key3.3.1' => '789',
                            'key3.3.2' => '  gHi  ',
                        ],
                    ],
                ],
            ],
            'tests clean if data is a mixed array' => [
                'data' => ['key1' => '  123  ', 'key2' => '  abc  ', '  456  ', '  def  '],
                'except' => [],
                'expectedResult' => ['key1' => '123', 'key2' => 'abc', '456', 'def'],
            ],
        ];
    }

    /**
     * @param mixed $data
     * @param mixed[]  $except
     * @param mixed $expectedResult
     *
     * @dataProvider provideDataForClean
     */
    public function testCleanSucceeds($data, array $except, $expectedResult): void
    {
        $cleaner = new TrimStringsRecursive();

        $result = $cleaner->clean($data, $except);

        self::assertSame($expectedResult, $result);
    }
}
