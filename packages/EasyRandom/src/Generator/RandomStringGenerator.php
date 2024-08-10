<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Generator;

use EonX\EasyRandom\ValueObject\RandomString;

final class RandomStringGenerator implements RandomStringGeneratorInterface
{
    public function generate(int $length): RandomString
    {
        return new RandomString($length);
    }
}
