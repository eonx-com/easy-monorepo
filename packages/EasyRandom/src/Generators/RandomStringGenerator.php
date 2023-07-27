<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Generators;

use EonX\EasyRandom\Interfaces\RandomStringGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomStringInterface;
use EonX\EasyRandom\ValueObject\RandomString;

final class RandomStringGenerator implements RandomStringGeneratorInterface
{
    public function generate(int $length): RandomStringInterface
    {
        return new RandomString($length);
    }
}
