<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\DBAL\Types;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use EonX\EasyDoctrine\DBAL\Types\CarbonImmutableDateType;
use EonX\EasyDoctrine\Tests\AbstractTestCase;

/**
 * @covers \EonX\EasyDoctrine\DBAL\Types\CarbonImmutableDateType
 */
final class CarbonImmutableDateTypeTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testConvertToPhpValueSucceeds
     */
    public function provideConvertToPhpValues(): iterable
    {
        $datetime = new DateTimeImmutable();
        $datetime = $datetime->setTime(0, 0, 0, 0);

        yield 'null value' => [null, null];

        yield 'DateTimeInterface object' => [
            $datetime,
            $datetime,
        ];

        yield 'date string' => [
            $datetime->format('Y-m-d'),
            $datetime,
        ];
    }

    /**
     * @param mixed $value
     *
     * @dataProvider provideConvertToPhpValues
     */
    public function testConvertToPhpValueSucceeds($value, ?DateTimeInterface $expectedValue = null): void
    {
        /** @var \EonX\EasyDoctrine\DBAL\Types\CarbonImmutableDateType $type */
        $type = Type::getType((new CarbonImmutableDateType())->getName());
        $platform = $this->prophesize(AbstractPlatform::class);
        $platform->getDateFormatString()->willReturn('Y-m-d');

        $phpValue = $type->convertToPHPValue($value, $platform->reveal());

        self::assertEquals($expectedValue, $phpValue);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Type::overrideType((new CarbonImmutableDateType())->getName(), CarbonImmutableDateType::class);
    }
}
