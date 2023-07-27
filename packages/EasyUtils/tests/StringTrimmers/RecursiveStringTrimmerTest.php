<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests\StringTrimmers;

use EonX\EasyUtils\StringTrimmers\RecursiveStringTrimmer;
use EonX\EasyUtils\Tests\AbstractTestCase;
use EonX\EasyUtils\Tests\Stubs\ToStringStub;
use stdClass;

final class RecursiveStringTrimmerTest extends AbstractTestCase
{
    /**
     * @see testCleanSucceeds
     */
    public static function provideDataForClean(): iterable
    {
        $object1 = new stdClass();
        $object2 = new ToStringStub();
        yield 'data is a string' => [
            'data' => " \t\n\r\0\x0B" . '123' . \PHP_EOL,
            'exceptKeys' => [],
            'expectedResult' => '123',
        ];
        yield 'data is an integer' => [
            'data' => 123,
            'exceptKeys' => [],
            'expectedResult' => 123,
        ];
        yield 'data is a bool' => [
            'data' => true,
            'exceptKeys' => [],
            'expectedResult' => true,
        ];
        yield 'data is an object' => [
            'data' => $object1,
            'exceptKeys' => [],
            'expectedResult' => $object1,
        ];
        yield 'data is an object with `__toString`' => [
            'data' => $object2,
            'exceptKeys' => [],
            'expectedResult' => $object2,
        ];
        yield 'data is an array of objects' => [
            'data' => [$object1, $object2],
            'exceptKeys' => [],
            'expectedResult' => [$object1, $object2],
        ];
        yield 'data is a float' => [
            'data' => 1.23,
            'exceptKeys' => [],
            'expectedResult' => 1.23,
        ];
        yield 'data is an array' => [
            'data' => ['  123  ', '  abc  ', 123],
            'exceptKeys' => [],
            'expectedResult' => ['123', 'abc', 123],
        ];
        yield 'data is an assoc array' => [
            'data' => [
                'key1' => '  123  ',
                'key2' => '  abc  ',
            ],
            'exceptKeys' => [],
            'expectedResult' => [
                'key1' => '123',
                'key2' => 'abc',
            ],
        ];
        yield 'data is an assoc array (exceptKeys key1)' => [
            'data' => [
                'key1' => '  123  ',
                'key2' => '  abc  ',
            ],
            'exceptKeys' => ['key1'],
            'expectedResult' => [
                'key1' => '  123  ',
                'key2' => 'abc',
            ],
        ];
        yield 'data is a multidimensional assoc array' => [
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
            'exceptKeys' => [],
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
        ];
        yield 'data is a multidimensional assoc array (exceptKeys key3.key3.3.key3.3.2)' => [
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
            'exceptKeys' => ['key3.key3.3.key3.3.2'],
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
        ];
        yield 'data is a mixed array' => [
            'data' => [
                'key1' => '  123  ',
                'key2' => '  abc  ',
                '  456  ',
                '  def  ',
            ],
            'exceptKeys' => [],
            'expectedResult' => [
                'key1' => '123',
                'key2' => 'abc',
                '456',
                'def',
            ],
        ];
    }

    /**
     * @param string[] $exceptKeys
     *
     * @dataProvider provideDataForClean
     */
    public function testCleanSucceeds(mixed $data, array $exceptKeys, mixed $expectedResult): void
    {
        $cleaner = new RecursiveStringTrimmer();

        $result = $cleaner->trim($data, $exceptKeys);

        self::assertSame($expectedResult, $result);
    }
}
