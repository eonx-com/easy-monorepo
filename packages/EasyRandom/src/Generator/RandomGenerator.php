<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Generator;

use EonX\EasyRandom\ValueObject\RandomStringConfig;

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

    public function string(RandomStringConfig $randomStringConfig): string
    {
        return $this->randomStringGenerator->generate($randomStringConfig);
    }

    public function uuid(): string
    {
        return $this->uuidGenerator->generate();
    }
}
