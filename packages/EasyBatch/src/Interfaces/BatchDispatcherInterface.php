<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchDispatcherInterface
{
    public function dispatch(BatchInterface $batch): void;
}
