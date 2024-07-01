<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Strategy;

use EonX\EasyRandom\Generator\RandomGeneratorInterface;

final readonly class UuidStrategy implements BatchObjectIdStrategyInterface
{
    public function __construct(
        private RandomGeneratorInterface $randomGenerator,
    ) {
    }

    public function generateId(): string
    {
        return $this->randomGenerator->uuid();
    }
}
