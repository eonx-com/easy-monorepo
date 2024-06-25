<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Fixture\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use EonX\EasyDoctrine\Tests\Fixture\ValueObject\Price;

final class PriceType extends StringType
{
    public const NAME = 'PRICE';

    public function convertToPHPValue($value, AbstractPlatform $platform): Price
    {
        $price = \explode(' ', (string)$value);

        return new Price($price[0], $price[1]);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
