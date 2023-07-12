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
     * @return mixed[]
     *
     * @see testCleanSucceeds
     */
    public static function provideDataForClean(): array
    {
        $object1 = new stdClass();
        $object2 = new ToStringStub();

        return [
            'data is a string' => [
                'data' => " \t\n\r\0\x0B" . '123' . \PHP_EOL,
                'exceptKeys' => [],
                'expectedResult' => '123',
            ],
            'data is an integer' => [
                'data' => 123,
                'exceptKeys' => [],
                'expectedResult' => 123,
            ],
            'data is a bool' => [
                'data' => true,
                'exceptKeys' => [],
                'expectedResult' => true,
            ],
            'data is an object' => [
                'data' => $object1,
                'exceptKeys' => [],
                'expectedResult' => $object1,
            ],
            'data is an object with `__toString`' => [
                'data' => $object2,
                'exceptKeys' => [],
                'expectedResult' => $object2,
            ],
            'data is an array of objects' => [
                'data' => [$object1, $object2],
                'exceptKeys' => [],
                'expectedResult' => [$object1, $object2],
            ],
            'data is a float' => [
                'data' => 1.23,
                'exceptKeys' => [],
                'expectedResult' => 1.23,
            ],
            'data is an array' => [
                'data' => ['  123  ', '  abc  ', 123],
                'exceptKeys' => [],
                'expectedResult' => ['123', 'abc', 123],
            ],
            'data is an assoc array' => [
                'data' => [
                    'key1' => '  123  ',
                    'key2' => '  abc  ',
                ],
                'exceptKeys' => [],
                'expectedResult' => [
                    'key1' => '123',
                    'key2' => 'abc',
                ],
            ],
            'data is an assoc array (exceptKeys key1)' => [
                'data' => [
                    'key1' => '  123  ',
                    'key2' => '  abc  ',
                ],
                'exceptKeys' => ['key1'],
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
            ],
            'data is a multidimensional assoc array (exceptKeys key3.key3.3.key3.3.2)' => [
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
            ],
            'data is a mixed array' => [
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
