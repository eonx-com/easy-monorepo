<?php

declare(strict_types=1);

namespace EonX\EasyBatch\IdStrategies;

use EonX\EasyBatch\Interfaces\BatchObjectIdStrategyInterface;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;

final class UuidV4Strategy implements BatchObjectIdStrategyInterface
{
    /**
     * @var \EonX\EasyRandom\Interfaces\RandomGeneratorInterface
     */
    private $randomGenerator;

    public function __construct(RandomGeneratorInterface $randomGenerator)
    {
        $this->randomGenerator = $randomGenerator;
    }

    public function generateId(): string
    {
        return $this->randomGenerator->uuidV4();
    }
}
