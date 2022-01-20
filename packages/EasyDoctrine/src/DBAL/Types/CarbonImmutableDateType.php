<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\DBAL\Types;

use Carbon\CarbonImmutable;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateImmutableType;

final class CarbonImmutableDateType extends DateImmutableType
{
    /**
     * @param mixed $value
     *
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?CarbonImmutable
    {
        $value = parent::convertToPHPValue($value, $platform);
        if ($value === null) {
            return null;
        }

        return CarbonImmutable::instance($value);
    }
}
