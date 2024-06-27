<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Strategy;

interface BatchObjectIdStrategyInterface
{
    public function generateId(): int|string;
}
