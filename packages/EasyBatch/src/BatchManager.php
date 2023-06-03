<?php

declare(strict_types=1);

namespace EonX\EasyBatch;

use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchManagerInterface;
use EonX\EasyBatch\Interfaces\BatchObjectManagerInterface;

/**
 * @deprecated since 4.0, will be removed in 5.0. Use EonX\EasyBatch\Interfaces\BatchObjectManager instead.
 */
final class BatchManager implements BatchManagerInterface
{
    public function __construct(
        private readonly BatchObjectManagerInterface $batchObjectManager,
    ) {
    }

    public function dispatch(BatchInterface $batch, ?callable $beforeFirstDispatch = null): BatchInterface
    {
        @\trigger_error(\sprintf(
            '%s::dispatch() is deprecated since 4.0, will be removed in 5.0. Use %s::dispatchBatch() instead.',
            BatchManagerInterface::class,
            BatchObjectManagerInterface::class
        ), \E_USER_DEPRECATED);

        return $this->batchObjectManager->dispatchBatch($batch, $beforeFirstDispatch);
    }
}
