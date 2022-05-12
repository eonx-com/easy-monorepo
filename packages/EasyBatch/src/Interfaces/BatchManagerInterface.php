<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

/**
 * @deprecated since 4.0, will be removed in 5.0. Use EonX\EasyBatch\Interfaces\BatchObjectManagerInterface instead.
 */
interface BatchManagerInterface
{
    public function dispatch(BatchInterface $batch, ?callable $beforeFirstDispatch = null): BatchInterface;
}
