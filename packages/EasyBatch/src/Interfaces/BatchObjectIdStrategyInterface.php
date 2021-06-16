<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchObjectIdStrategyInterface
{
    /**
     * @return int|string
     */
    public function generateId();
}
