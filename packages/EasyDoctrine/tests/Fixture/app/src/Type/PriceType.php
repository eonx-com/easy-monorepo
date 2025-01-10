<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Fixture\App\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use EonX\EasyDoctrine\Tests\Fixture\App\ValueObject\Price;

final class PriceType extends StringType
{
    public const NAME = 'price';

    /**
     * @param int|string|null $value
     */
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
