<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Helpers;

use EonX\EasyCore\Helpers\RecursiveStringsTrimmer;
use EonX\EasyCore\Tests\AbstractTestCase;
use EonX\EasyCore\Tests\Bridge\Symfony\Stubs\ToStringStub;
use stdClass;

/**
 * @covers \EonX\EasyCore\Helpers\RecursiveStringsTrimmer
 */
final class RecursiveStringsTrimmerTest extends AbstractTestCase
{
    /**
     * @return mixed[]
     *
     * @see testCleanSucceeds
     */
    public function provideDataForClean(): array
    {
        $object1 = new stdClass();
        $object2 = new ToStringStub();

        return [
            'data is a string' => [
                'data' => " \t\n\r\0\x0B" . '123' . \PHP_EOL,
                'except' => [],
                'expectedResult' => '123',
            ],
            'data is an integer' => [
                'data' => 123,
                'except' => [],
                'expectedResult' => 123,
            ],
            'data is a bool' => [
                'data' => true,
                'except' => [],
                'expectedResult' => true,
            ],
            'data is an object' => [
                'data' => $object1,
                'except' => [],
                'expectedResult' => $object1,
            ],
            'data is an object with `__toString`' => [
                'data' => $object2,
                'except' => [],
                'expectedResult' => $object2,
            ],
            'data is an array of objects' => [
                'data' => [$object1, $object2],
                'except' => [],
                'expectedResult' => [$object1, $object2],
            ],
            'data is a float' => [
                'data' => 1.23,
                'except' => [],
                'expectedResult' => 1.23,
            ],
            'data is an array' => [
                'data' => ['  123  ', '  abc  ', 123],
                'except' => [],
                'expectedResult' => ['123', 'abc', 123],
            ],
            'data is an assoc array' => [
                'data' => [
                    'key1' => '  123  ',
                    'key2' => '  abc  ',
                ],
                'except' => [],
                'expectedResult' => [
                    'key1' => '123',
                    'key2' => 'abc',
                ],
            ],
            'data is an assoc array (except key1)' => [
                'data' => [
                    'key1' => '  123  ',
                    'key2' => '  abc  ',
                ],
                'except' => ['key1'],
                'expectedResult' => [
                    'key1' => '  123  ',
                    'key2' => 'abc',
                ],
            ],
            'data is a multidimensional assoc array' => [
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
            'data is a multidimensional assoc array (except key3.key3.3.key3.3.2)' => [
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
            'data is a mixed array' => [
                'data' => [
                    'key1' => '  123  ',
                    'key2' => '  abc  ',
                    '  456  ',
                    '  def  ',
                ],
                'except' => [],
                'expectedResult' => [
                    'key1' => '123',
                    'key2' => 'abc',
                    '456',
                    'def',
                ],
            ],
        ];
    }

    /**
     * @param mixed $data
     * @param string[] $except
     * @param mixed $expectedResult
     *
     * @dataProvider provideDataForClean
     */
    public function testCleanSucceeds($data, array $except, $expectedResult): void
    {
        $cleaner = new RecursiveStringsTrimmer();

        $result = $cleaner->trim($data, $except);

        self::assertSame($expectedResult, $result);
    }
}
