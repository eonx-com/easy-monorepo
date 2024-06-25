<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\Common\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use EonX\EasyDoctrine\Common\Type\JsonbType;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(JsonbType::class)]
final class JsonbTypeTest extends AbstractUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Type::hasType(JsonbType::JSONB) === false) {
            Type::addType(JsonbType::JSONB, JsonbType::class);
        }
    }

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

    /**
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    #[DataProvider('provideConvertToDatabaseValues')]
    public function testConvertToDatabaseValueSucceeds(mixed $phpValue, ?string $postgresValue = null): void
    {
        /** @var \EonX\EasyDoctrine\Common\Type\JsonbType $type */
        $type = Type::getType(JsonbType::JSONB);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $result = $type->convertToDatabaseValue($phpValue, $platform);

        self::assertSame($postgresValue, $result);
    }

    /**
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function testConvertToDatabaseValueThrowsConversionException(): void
    {
        /** @var \EonX\EasyDoctrine\Common\Type\JsonbType $type */
        $type = Type::getType(JsonbType::JSONB);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();
        $value = \urldecode('some incorrectly encoded utf string %C4');
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage(
            "Could not convert PHP type 'string' to 'jsonb', as an " .
            "'Malformed UTF-8 characters, possibly incorrectly encoded' error was triggered by the serialization"
        );

        $type->convertToDatabaseValue($value, $platform);
    }

    /**
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    #[DataProvider('provideConvertToPhpValues')]
    public function testConvertToPhpValueSucceeds(mixed $phpValue, ?string $postgresValue = null): void
    {
        /** @var \EonX\EasyDoctrine\Common\Type\JsonbType $type */
        $type = Type::getType(JsonbType::JSONB);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();

        $result = $type->convertToPHPValue($postgresValue, $platform);

        self::assertSame($phpValue, $result);
    }

    /**
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function testConvertToPhpValueThrowsConversionException(): void
    {
        /** @var \EonX\EasyDoctrine\Common\Type\JsonbType $type */
        $type = Type::getType(JsonbType::JSONB);
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->prophesize(AbstractPlatform::class)->reveal();
        $value = 'ineligible-value';
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert database value "ineligible-value" to Doctrine Type jsonb');

        $type->convertToPHPValue($value, $platform);
    }

    public function testGetNameSucceeds(): void
    {
        /** @var \EonX\EasyDoctrine\Common\Type\JsonbType $type */
        $type = Type::getType(JsonbType::JSONB);

        $name = $type->getName();

        self::assertSame(JsonbType::JSONB, $name);
    }

    public function testGetSQLDeclaration(): void
    {
        /** @var \EonX\EasyDoctrine\Common\Type\JsonbType $type */
        $type = Type::getType(JsonbType::JSONB);
        $platform = $this->prophesize(AbstractPlatform::class);
        $platform->getDoctrineTypeMapping($type::JSONB)->willReturn($type::JSONB);

        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platformReveal */
        $platformReveal = $platform->reveal();
        $result = $type->getSQLDeclaration([], $platformReveal);

        self::assertSame($type::FORMAT_DB_JSONB, $result);
    }
}
