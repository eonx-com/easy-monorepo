<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Generator;

use EonX\EasyRandom\ValueObject\RandomString;

final readonly class RandomGenerator implements RandomGeneratorInterface
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

    public function string(int $length): RandomString
    {
        return $this->randomStringGenerator->generate($length);
    }

    public function uuid(): string
    {
        return $this->uuidGenerator->generate();
    }
}
