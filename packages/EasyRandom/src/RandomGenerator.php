<?php

declare(strict_types=1);

namespace EonX\EasyRandom;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomStringInterface;

final class RandomGenerator implements RandomGeneratorInterface
{
    public function randomInteger(?int $min = null, ?int $max = null): int
    {
        $min = $min ?? 0;
        $max = $max ?? \PHP_INT_MAX;

        try {
            return \random_int($min, $max);
            // @codeCoverageIgnoreStart
        } catch (\Throwable $throwable) {
            // It's unlikely exception will be thrown as system is running *nix
            return \mt_rand($min, $max);
            // @codeCoverageIgnoreEnd
        }
    }

    public function randomString(int $length): RandomStringInterface
    {
        return new RandomString($length);
    }
}
