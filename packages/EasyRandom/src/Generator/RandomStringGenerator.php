<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Generator;

use EonX\EasyRandom\ValueObject\RandomString;
use EonX\EasyRandom\ValueObject\RandomStringInterface;

final class RandomStringGenerator implements RandomStringGeneratorInterface
{
    public function generate(int $length): RandomStringInterface
    {
        return new RandomString($length);
    }
}
