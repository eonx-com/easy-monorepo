<?php

declare(strict_types=1);

namespace EonX\EasyRandom;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomStringInterface;

final class RandomGenerator implements RandomGeneratorInterface
{
    public function randomInteger(?int $min = null, ?int $max = null): int
    {
        return \random_int($min ?? 0, $max ?? \PHP_INT_MAX);
    }

    public function randomString(int $length): RandomStringInterface
    {
        return new RandomString($length);
    }
}
