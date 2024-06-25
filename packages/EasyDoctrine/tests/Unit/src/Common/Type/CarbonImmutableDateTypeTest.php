<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\Common\Type;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use EonX\EasyDoctrine\Common\Type\CarbonImmutableDateType;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CarbonImmutableDateType::class)]
final class CarbonImmutableDateTypeTest extends AbstractUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Type::overrideType((new CarbonImmutableDateType())->getName(), CarbonImmutableDateType::class);
    }

    /**
     * @see testConvertToPhpValueSucceeds
     */
    public static function provideConvertToPhpValues(): iterable
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

    #[DataProvider('provideConvertToPhpValues')]
    public function testConvertToPhpValueSucceeds(mixed $value, ?DateTimeInterface $expectedValue = null): void
    {
        /** @var \EonX\EasyDoctrine\Common\Type\CarbonImmutableDateType $type */
        $type = Type::getType((new CarbonImmutableDateType())->getName());
        $platform = $this->prophesize(AbstractPlatform::class);
        $platform->getDateFormatString()
            ->willReturn('Y-m-d');
        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $platform->reveal();

        $phpValue = $type->convertToPHPValue($value, $platform);

        self::assertEquals($expectedValue, $phpValue);
    }
}
