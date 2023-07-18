<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Generators;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomStringGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomStringInterface;
use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;

final class RandomGenerator implements RandomGeneratorInterface
{
    public function __construct(
        private RandomStringGeneratorInterface $randomStringGenerator,
        private RandomIntegerGeneratorInterface $randomIntegerGenerator,
        private UuidGeneratorInterface $uuidV4Generator
    ) {
    }

    public function integer(?int $min = null, ?int $max = null): int
    {
        return $this->randomIntegerGenerator->generate($min, $max);
    }

    public function string(int $length): RandomStringInterface
    {
        return $this->randomStringGenerator->generate($length);
    }

    public function uuid(): string
    {
        return $this->uuidV4();
    }

    public function uuidV4(): string
    {
        return $this->uuidV4Generator->generate();
    }
}
