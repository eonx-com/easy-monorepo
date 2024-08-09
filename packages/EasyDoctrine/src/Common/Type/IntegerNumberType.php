<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Common\Type;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Type;
use EonX\EasyUtils\Math\ValueObject\Number;

final class IntegerNumberType extends Type
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

        throw InvalidType::new($value, self::class, ['null', Number::class]);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Number
    {
        if ($value === null) {
            return null;
        }

        $value = parent::convertToPHPValue($value, $platform);

        return new Number($value);
    }

    public function getBindingType(): ParameterType
    {
        return ParameterType::STRING;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getBigIntTypeDeclarationSQL($column);
    }
}
