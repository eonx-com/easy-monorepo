<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Fixtures;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class PriceType extends StringType
{
    public const NAME = 'PRICE';

    public function convertToPHPValue($value, AbstractPlatform $platform): Price
    {
        $price = \explode(' ', $value);

        return new Price($price[0], $price[1]);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
