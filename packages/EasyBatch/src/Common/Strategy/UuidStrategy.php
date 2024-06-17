<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Strategy;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;

final class UuidStrategy implements BatchObjectIdStrategyInterface
{
    public function __construct(
        private readonly RandomGeneratorInterface $randomGenerator,
    ) {
    }

    public function generateId(): string
    {
        return $this->randomGenerator->uuid();
    }
}
