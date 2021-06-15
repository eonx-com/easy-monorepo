<?php

declare(strict_types=1);

namespace EonX\EasyBatch;

use Carbon\Carbon;
use EonX\EasyBatch\Events\BatchCompletedEvent;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchUpdaterInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class BatchUpdater implements BatchUpdaterInterface
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchRepositoryInterface
     */
    private $batchRepository;

    /**
     * @var \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(BatchRepositoryInterface $batchRepository, EventDispatcherInterface $dispatcher)
    {
        $this->batchRepository = $batchRepository;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchIdRequiredException
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     * @throws \Throwable
     */
    public function updateForItem(BatchInterface $batch, BatchItemInterface $batchItem): BatchInterface
    {
        $update = function (BatchInterface $freshBatch) use ($batchItem): BatchInterface {
            if ($this->shouldUpdateProcessedCount($batchItem)) {
                $freshBatch->setProcessed($freshBatch->countProcessed() + 1);
            }

            // Forget about previous fail, and see what happens this time
            if ($batchItem->isRetried()) {
                $freshBatch->setFailed($freshBatch->countFailed() - 1);
            }

            switch ($batchItem->getStatus()) {
                case BatchItemInterface::STATUS_FAILED:
                    $freshBatch->setFailed($freshBatch->countFailed() + 1);
                    break;
                case BatchItemInterface::STATUS_SUCCESS:
                    $freshBatch->setSucceeded($freshBatch->countSucceeded() + 1);
            }

            // Start the batch timer
            if ($freshBatch->getStartedAt() === null) {
                $freshBatch->setStartedAt(Carbon::now('UTC'));
                $freshBatch->setStatus(BatchInterface::STATUS_PROCESSING);
            }

            return $this->handleBatchStatus($freshBatch);
        };

        return $this->updateAtomic($batch, $update);
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchIdRequiredException
     */
    public function updateTotal(BatchInterface $batch, int $add): BatchInterface
    {
        $update = function (BatchInterface $freshBatch) use ($add): BatchInterface {
            $freshBatch->setTotal($freshBatch->countTotal() + $add);

            return $this->handleBatchStatus($freshBatch);
        };

        return $this->updateAtomic($batch, $update);
    }

    private function handleBatchStatus(BatchInterface $batch): BatchInterface
    {
        // Last item of the batch
        if ($batch->countTotal() === $batch->countProcessed()) {
            $batch->setFinishedAt(Carbon::now('UTC'));
            $batch->setStatus(
                $batch->countFailed() > 0 ? BatchInterface::STATUS_FAILED : BatchInterface::STATUS_SUCCESS
            );
        }

        // Handle previously completed batch
        if ($batch->isCompleted() === false && $batch->countProcessed() > 0) {
            $batch->setStatus(BatchInterface::STATUS_PROCESSING);
        }

        return $batch;
    }

    private function shouldUpdateProcessedCount(BatchItemInterface $batchItem): bool
    {
        // Update processed only on first attempt and/or if not pending approval
        return $batchItem->isRetried() === false && $batchItem->isCompleted();
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchIdRequiredException
     */
    private function updateAtomic(BatchInterface $batch, callable $func): BatchInterface
    {
        $batch = $this->batchRepository->updateAtomic($batch, $func);

        // Dispatch completed event if needed
        if ($batch->isCompleted()) {
            $this->dispatcher->dispatch(new BatchCompletedEvent($batch));
        }

        return $batch;
    }
}
