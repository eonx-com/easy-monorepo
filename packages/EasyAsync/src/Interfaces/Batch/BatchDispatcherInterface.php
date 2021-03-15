<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

interface BatchDispatcherInterface
{
    public function dispatch(BatchInterface $batch): void;
}
