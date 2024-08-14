<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Processor;

use Carbon\Carbon;
use EonX\EasyBatch\Common\Enum\BatchObjectStatus;
use EonX\EasyBatch\Common\Event\BatchCancelledEvent;
use EonX\EasyBatch\Common\Event\BatchCompletedEvent;
use EonX\EasyBatch\Common\Event\BatchItemCancelledEvent;
use EonX\EasyBatch\Common\Event\BatchItemCompletedEvent;
use EonX\EasyBatch\Common\Manager\BatchObjectManagerInterface;
use EonX\EasyBatch\Common\Repository\BatchItemRepositoryInterface;
use EonX\EasyBatch\Common\Repository\BatchRepositoryInterface;
use EonX\EasyBatch\Common\ValueObject\Batch;
use EonX\EasyBatch\Common\ValueObject\BatchItem;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;

final class BatchProcessor
{
    /**
     * @var bool[]
     */
    private array $cache = [];

    public function __construct(
        private readonly BatchItemRepositoryInterface $batchItemRepository,
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemNotFoundException
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
     */
    public function processBatchForBatchItem(
        BatchObjectManagerInterface $batchObjectManager,
        Batch $batch,
        BatchItem $batchItem,
        ?callable $updateFreshBatch = null,
    ): Batch {
        // Prevent same batchItem to be process twice within the same message lifecycle
        if (isset($this->cache[$batchItem->getIdOrFail()])) {
            return $batch;
        }
        $this->cache[$batchItem->getIdOrFail()] = true;

        $updateFunc = function (Batch $freshBatch) use ($batchItem, $updateFreshBatch): Batch {
            // Update counts only when processed not matching total
            if ($freshBatch->countProcessed() < $freshBatch->countTotal()) {
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
            }

            $this->updateCommonBatchProperties($freshBatch);

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

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemNotFoundException
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
     */
    public function restoreState(BatchObjectManagerInterface $batchObjectManager, Batch $batch): Batch
    {
        $restoreFunc = function (Batch $freshBatch): Batch {
            $counts = $this->batchItemRepository->findCountsForBatch($freshBatch->getIdOrFail());

            $freshBatch
                ->setCancelled($counts->countCancelled())
                ->setFailed($counts->countFailed())
                ->setProcessed($counts->countProcessed())
                ->setSucceeded($counts->countSucceeded())
                ->setTotal($counts->countTotal());

            $this->updateCommonBatchProperties($freshBatch);

            return $freshBatch;
        };

        $freshBatch = $this->batchRepository->updateAtomic($batch, $restoreFunc);

        if ($this->areBatchesIdentical($batch, $freshBatch) === false) {
            $this->handleParentBatchItem($batchObjectManager, $freshBatch);
            $this->dispatchBatchEvent($freshBatch);
        }

        return $freshBatch;
    }

    private function areBatchesIdentical(Batch $batch1, Batch $batch2): bool
    {
        return $batch1->countCancelled() === $batch2->countCancelled()
            && $batch1->countFailed() === $batch2->countFailed()
            && $batch1->countProcessed() === $batch2->countProcessed()
            && $batch1->countSucceeded() === $batch2->countSucceeded()
            && $batch1->countTotal() === $batch2->countTotal()
            && $batch1->getStatus() === $batch2->getStatus();
    }

    private function dispatchBatchEvent(Batch $batch): void
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

    private function dispatchBatchItemEvent(BatchItem $batchItem): void
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
        BatchItem $batchItem,
    ): void {
        $currentStatus = $batchItem->getStatus();

        $batchItem->setStatus(BatchObjectStatus::ProcessingDependentObjects);

        if ($currentStatus === BatchObjectStatus::Succeeded) {
            $batchObjectManager->approve($batchItem);
        }

        if (\in_array($currentStatus, BatchObjectStatus::STATUSES_FOR_CANCEL, true)) {
            $batchObjectManager->cancel($batchItem);
        }
    }

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemNotFoundException
     */
    private function handleParentBatchItem(BatchObjectManagerInterface $batchObjectManager, Batch $batch): void
    {
        if ($batch->getParentBatchItemId() === null) {
            return;
        }

        if ($batch->isSucceeded()) {
            // Change status to pending approval to trick batchObjectManager
            $batch->setStatus(BatchObjectStatus::SucceededPendingApproval);

            $batchObjectManager->approve($batch);
        }

        if ($batch->isCancelled() || $batch->isFailed()) {
            $batchObjectManager->cancel($this->batchItemRepository->findOrFail($batch->getParentBatchItemId()));
        }
    }

    private function updateCommonBatchProperties(Batch $freshBatch): void
    {
        // Start the batch timer
        if ($freshBatch->getStartedAt() === null) {
            $freshBatch->setStartedAt(Carbon::now('UTC'));
            $freshBatch->setStatus(BatchObjectStatus::Processing);
        }

        // Last item of the batch
        if ($freshBatch->countTotal() === $freshBatch->countProcessed()) {
            $freshBatch->setFinishedAt($freshBatch->getFinishedAt() ?? Carbon::now('UTC'));

            // All items are cancelled, cancel batch
            if ($freshBatch->countCancelled() === $freshBatch->countTotal()) {
                $freshBatch
                    ->setCancelledAt($freshBatch->getCancelledAt() ?? Carbon::now('UTC'))
                    ->setStatus(BatchObjectStatus::Cancelled);
            }

            // If batch not cancelled from statement above, set status
            if ($freshBatch->isCancelled() === false) {
                // Batch failed if not all items succeeded
                $freshBatch->setStatus(
                    $freshBatch->countSucceeded() < $freshBatch->countTotal()
                        ? BatchObjectStatus::Failed
                        : BatchObjectStatus::Succeeded
                );
            }
        }

        // Handle previously completed batch
        if ($freshBatch->isCompleted() === false && $freshBatch->countProcessed() > 0) {
            $freshBatch->setStatus(BatchObjectStatus::Processing);
        }

        // Handle approval required
        if ($freshBatch->isSucceeded() && $freshBatch->isApprovalRequired()) {
            $freshBatch->setStatus(BatchObjectStatus::SucceededPendingApproval);
        }
    }
}
