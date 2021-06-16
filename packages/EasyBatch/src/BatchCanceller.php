<?php

declare(strict_types=1);

namespace EonX\EasyBatch;

use Carbon\Carbon;
use EonX\EasyBatch\Events\BatchCancelledEvent;
use EonX\EasyBatch\Interfaces\BatchCancellerInterface;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class BatchCanceller implements BatchCancellerInterface
{
    /**
     * @var \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function cancel(BatchInterface $batch, ?\Throwable $throwable = null): BatchInterface
    {
        $batch
            ->setCancelledAt(Carbon::now('UTC'))
            ->setStatus(BatchInterface::STATUS_CANCELLED);

        if ($throwable !== null) {
            $batch->setThrowable($throwable);
        }

        $this->dispatcher->dispatch(new BatchCancelledEvent($batch));

        return $batch;
    }
}
