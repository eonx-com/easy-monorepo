<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Common\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\BigIntType as DoctrineBigIntType;
use Doctrine\DBAL\Types\ConversionException;
use EonX\EasyUtils\Math\ValueObject\Number;

final class IntegerNumberType extends DoctrineBigIntType
{
    public const NAME = 'integer_number';

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Number) {
            return (string)$value;
        }

        throw ConversionException::conversionFailedInvalidType(
            $value,
            $this->getName(),
            ['null', Number::class]
        );
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Number
    {
        if ($value === null) {
            return null;
        }
        $value = parent::convertToPHPValue($value, $platform);

        return new Number($value);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}