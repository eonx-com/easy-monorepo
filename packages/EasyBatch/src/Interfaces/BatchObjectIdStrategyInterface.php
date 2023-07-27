<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchObjectIdStrategyInterface
{
    public function generateId(): int|string;
}
