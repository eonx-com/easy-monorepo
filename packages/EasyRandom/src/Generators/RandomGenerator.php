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
        private UuidGeneratorInterface $uuidGenerator,
        private RandomIntegerGeneratorInterface $randomIntegerGenerator = new RandomIntegerGenerator(),
        private RandomStringGeneratorInterface $randomStringGenerator = new RandomStringGenerator(),
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
        return $this->uuidGenerator->generate();
    }
}
