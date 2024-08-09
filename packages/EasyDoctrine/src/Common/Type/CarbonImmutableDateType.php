<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Common\Type;

use Carbon\CarbonImmutable;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateImmutableType;

final class CarbonImmutableDateType extends DateImmutableType
{
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?CarbonImmutable
    {
        $value = parent::convertToPHPValue($value, $platform);
        if ($value === null) {
            return null;
        }

        return CarbonImmutable::instance($value);
    }
}
