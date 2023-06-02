<?php

declare(strict_types=1);

namespace EonX\EasyBatch\IdStrategies;

use EonX\EasyBatch\Interfaces\BatchObjectIdStrategyInterface;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;

final class UuidV4Strategy implements BatchObjectIdStrategyInterface
{
    public function __construct(
        private readonly RandomGeneratorInterface $randomGenerator
    ) {
    }

    public function generateId(): string
    {
        return $this->randomGenerator->uuidV4();
    }
}
