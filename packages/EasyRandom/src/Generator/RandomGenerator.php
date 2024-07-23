<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Generator;

use EonX\EasyRandom\ValueObject\RandomStringInterface;

final class RandomGenerator implements RandomGeneratorInterface
{
    public function __construct(
        private readonly UuidGeneratorInterface $uuidGenerator = new DefaultUuidGenerator(),
        private readonly RandomIntegerGeneratorInterface $randomIntegerGenerator = new RandomIntegerGenerator(),
        private readonly RandomStringGeneratorInterface $randomStringGenerator = new RandomStringGenerator(),
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
