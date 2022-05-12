<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Processors;

use Carbon\Carbon;
use EonX\EasyBatch\Events\BatchCancelledEvent;
use EonX\EasyBatch\Events\BatchCompletedEvent;
use EonX\EasyBatch\Events\BatchItemCancelledEvent;
use EonX\EasyBatch\Events\BatchItemCompletedEvent;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchObjectManagerInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class BatchProcessor
{
    /**
     * @var bool[]
     */
    private array $cache = [];

    public function __construct(
        private readonly BatchItemRepositoryInterface $batchItemRepository,
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemNotFoundException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    public function processBatchForBatchItem(
        BatchObjectManagerInterface $batchObjectManager,
        BatchInterface $batch,
        BatchItemInterface $batchItem,
        ?callable $updateFreshBatch = null
    ): BatchInterface {
        // Prevent same batchItem to be process twice within the same message lifecycle
        if (isset($this->cache[$batchItem->getIdOrFail()])) {
            return $batch;
        }
        $this->cache[$batchItem->getIdOrFail()] = true;

        $updateFunc = static function (BatchInterface $freshBatch) use ($batchItem, $updateFreshBatch): BatchInterface {
            if ($batchItem->isCompleted()) {
                $freshBatch->setProcessed($freshBatch->countProcessed() + 1);
            }

            if ($batchItem->isCancelled()) {
                $freshBatch->setCancelled($freshBatch->countCancelled() + 1);
            }

            if ($batchItem->isFailed()) {
                $freshBatch->setFailed($freshBatch->countFailed() + 1);
            }

            if ($batchItem->isSucceeded()) {
                $freshBatch->setSucceeded($freshBatch->countSucceeded() + 1);
            }

            // Start the batch timer
            if ($freshBatch->getStartedAt() === null) {
                $freshBatch->setStartedAt(Carbon::now('UTC'));
                $freshBatch->setStatus(BatchObjectInterface::STATUS_PROCESSING);
            }

            // Last item of the batch
            if ($freshBatch->countTotal() === $freshBatch->countProcessed()) {
                $freshBatch->setFinishedAt(Carbon::now('UTC'));

                // All items are cancelled, cancel batch
                if ($freshBatch->countCancelled() === $freshBatch->countTotal()) {
                    $freshBatch
                        ->setCancelledAt(Carbon::now('UTC'))
                        ->setStatus(BatchObjectInterface::STATUS_CANCELLED);
                }

                // If batch not cancelled from statement above, set status
                if ($freshBatch->isCancelled() === false) {
                    // Batch failed if not all items succeeded
                    $freshBatch->setStatus(
                        $freshBatch->countSucceeded() < $freshBatch->countTotal()
                            ? BatchObjectInterface::STATUS_FAILED
                            : BatchObjectInterface::STATUS_SUCCEEDED
                    );
                }
            }

            // Handle previously completed batch
            if ($freshBatch->isCompleted() === false && $freshBatch->countProcessed() > 0) {
                $freshBatch->setStatus(BatchObjectInterface::STATUS_PROCESSING);
            }

            // Handle approval required
            if ($freshBatch->isSucceeded() && $freshBatch->isApprovalRequired()) {
                $freshBatch->setStatus(BatchObjectInterface::STATUS_SUCCEEDED_PENDING_APPROVAL);
            }

            if ($updateFreshBatch !== null) {
                \call_user_func($updateFreshBatch, $freshBatch);
            }

            return $freshBatch;
        };

        $freshBatch = $this->batchRepository->updateAtomic($batch, $updateFunc);

        $this->handleBatchItemDependentObjects($batchObjectManager, $batchItem);
        $this->handleParentBatchItem($batchObjectManager, $freshBatch);
        $this->dispatchBatchItemEvent($batchItem);
        $this->dispatchBatchEvent($freshBatch);

        return $freshBatch;
    }

    public function reset(): self
    {
        $this->cache = [];

        return $this;
    }

    private function dispatchBatchEvent(BatchInterface $batch): void
    {
        // Dispatch batch cancelled event only once
        if ($batch->isCancelled() && $batch->countCancelled() === 1) {
            $this->eventDispatcher->dispatch(new BatchCancelledEvent($batch));

            return;
        }

        if ($batch->isCompleted()) {
            $this->eventDispatcher->dispatch(new BatchCompletedEvent($batch));
        }
    }

    private function dispatchBatchItemEvent(BatchItemInterface $batchItem): void
    {
        if ($batchItem->isCancelled()) {
            $this->eventDispatcher->dispatch(new BatchItemCancelledEvent($batchItem));

            return;
        }

        if ($batchItem->isCompleted()) {
            $this->eventDispatcher->dispatch(new BatchItemCompletedEvent($batchItem));
        }
    }

    private function handleBatchItemDependentObjects(
        BatchObjectManagerInterface $batchObjectManager,
        BatchItemInterface $batchItem
    ): void {
        $currentStatus = $batchItem->getStatus();
        $toCancelStatuses = [
            BatchObjectInterface::STATUS_CANCELLED,
            BatchObjectInterface::STATUS_FAILED,
        ];

        $batchItem->setStatus(BatchItemInterface::STATUS_PROCESSING_DEPENDENT_OBJECTS);

        if ($currentStatus === BatchObjectInterface::STATUS_SUCCEEDED) {
            $batchObjectManager->approve($batchItem);
        }

        if (\in_array($currentStatus, $toCancelStatuses, true)) {
            $batchObjectManager->cancel($batchItem);
        }
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemNotFoundException
     */
    private function handleParentBatchItem(BatchObjectManagerInterface $batchObjectManager, BatchInterface $batch): void
    {
        if ($batch->getParentBatchItemId() === null) {
            return;
        }

        if ($batch->isSucceeded()) {
            // Change status to pending approval to trick batchObjectManager
            $batch->setStatus(BatchObjectInterface::STATUS_SUCCEEDED_PENDING_APPROVAL);

            $batchObjectManager->approve($batch);
        }

        if ($batch->isCancelled() || $batch->isFailed()) {
            $batchObjectManager->cancel($this->batchItemRepository->findOrFail($batch->getParentBatchItemId()));
        }
    }
}
