<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

/**
 * @deprecated since 3.3, will be removed in 4.0. Use eonx-com/easy-batch instead.
 */
interface BatchCancellerInterface
{
    public function cancel(BatchInterface $batch, ?\Throwable $throwable = null): BatchInterface;
}
