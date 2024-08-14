<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\Common\Type;

use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use EonX\EasyDoctrine\Common\Type\JsonbType;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(JsonbType::class)]
final class JsonbTypeTest extends AbstractUnitTestCase
{
    /**
     * @see testConvertToDatabaseValueSucceeds
     */
    public static function provideConvertToDatabaseValues(): iterable
    {
        foreach (self::provideConvertToPhpValues() as $name => $data) {
            if ($name === 'multidimensional array phpValue') {
                yield $name => [
                    'phpValue' => [
                        'key3' => '15',
                        'key1' => 'value1',
                        'key4' => 15,
                        'key2' => false,
                        'key6' => [
                            'sub-key-2' => 'bar',
                            'sub-key-3' => 42,
                            'sub-key-1' => 'foo',
                        ],
                        'key5' => [112, 242, 309, 310],
                    ],
                    'postgresValue' => '{"key3":"15","key1":"value1","key4":15,"key2":false,' .
                        '"key6":{"sub-key-2":"bar","sub-key-3":42,"sub-key-1":"foo"},"key5":[112,242,309,310]}',
                ];

                continue;
            }

            yield $name => $data;
        }

        yield 'object phpValue' => [
            'phpValue' => (object)[
                'property' => 'value',
            ],
            'postgresValue' => '{"property":"value"}',
        ];
    }

    /**
     * @see testConvertToPhpValueSucceeds
     */
    public static function provideConvertToPhpValues(): iterable
    {
        yield 'null phpValue' => [
            'phpValue' => null,
            'postgresValue' => null,
        ];
        yield 'empty phpValue' => [
            'phpValue' => [],
            'postgresValue' => '[]',
        ];
        yield 'integer phpValue' => [
            'phpValue' => 13,
            'postgresValue' => '13',
        ];
        yield 'float phpValue' => [
            'phpValue' => 13.93,
            'postgresValue' => '13.93',
        ];
        yield 'string phpValue' => [
            'phpValue' => 'a string value',
            'postgresValue' => '"a string value"',
        ];
        yield 'array of integers phpValue' => [
            'phpValue' => [681, 1185, 1878, 1989],
            'postgresValue' => '[681,1185,1878,1989]',
        ];
        yield 'multidimensional array phpValue' => [
            'phpValue' => [
                'key1' => 'value1',
                'key2' => false,
                'key3' => '15',
                'key4' => 15,
                'key5' => [112, 242, 309, 310],
                'key6' => [
                    'sub-key-1' => 'foo',
                    'sub-key-2' => 'bar',
                    'sub-key-3' => 42,
                ],
            ],
            'postgresValue' => '{"key3":"15","key1":"value1","key4":15,"key2":false,' .
                '"key6":{"sub-key-2":"bar","sub-key-3":42,"sub-key-1":"foo"},"key5":[112,242,309,310]}',
        ];
    }

    #[DataProvider('provideConvertToDatabaseValues')]
    public function testConvertToDatabaseValueSucceeds(mixed $phpValue, ?string $postgresValue = null): void
    {
        $type = new JsonbType();
        $platform = new SQLitePlatform();

        $result = $type->convertToDatabaseValue($phpValue, $platform);

        self::assertSame($postgresValue, $result);
    }

    public function testConvertToDatabaseValueThrowsInvalidTypeException(): void
    {
        $type = new JsonbType();
        $platform = new SQLitePlatform();
        $value = \urldecode('some incorrectly encoded utf string %C4');
        $this->expectException(InvalidType::class);
        $this->expectExceptionMessage("Could not convert PHP value '" . $value . "'" .
            " to type EonX\EasyDoctrine\Common\Type\JsonbType. Expected one of the following types: mixed.");

        $type->convertToDatabaseValue($value, $platform);
    }

    #[DataProvider('provideConvertToPhpValues')]
    public function testConvertToPhpValueSucceeds(mixed $phpValue, ?string $postgresValue = null): void
    {
        $type = new JsonbType();
        $platform = new SQLitePlatform();

        $result = $type->convertToPHPValue($postgresValue, $platform);

        self::assertSame($phpValue, $result);
    }

    public function testConvertToPhpValueThrowsInvalidFormatException(): void
    {
        $type = new JsonbType();
        $platform = new SQLitePlatform();
        $value = 'ineligible-value';
        $this->expectException(InvalidFormat::class);
        $this->expectExceptionMessage('Could not convert database value "ineligible-value" to Doctrine' .
            ' Type EonX\EasyDoctrine\Common\Type\JsonbType. Expected format "json".');

        $type->convertToPHPValue($value, $platform);
    }

    public function testGetSQLDeclaration(): void
    {
        $type = new JsonbType();
        $platform = new SQLitePlatform();

        $result = $type->getSQLDeclaration([], $platform);

        self::assertSame('JSONB', $result);
    }
}
