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

            // Last item of the batch
            if ($freshBatch->countTotal() === $freshBatch->countProcessed()) {
                $freshBatch->setFinishedAt(Carbon::now('UTC'));
                $freshBatch->setStatus(
                    $freshBatch->countFailed() > 0 ? BatchInterface::STATUS_FAILED : BatchInterface::STATUS_SUCCESS
                );
            }

            return $freshBatch;
        };

        return $this->updateAtomic($batch, $update);
    }

    private function shouldUpdateProcessedCount(BatchItemInterface $batchItem): bool
    {
        // Update processed only on first attempt and/or if not pending approval
        return $batchItem->isRetried() === false
            && $batchItem->getStatus() !== BatchItemInterface::STATUS_SUCCESS_PENDING_APPROVAL;
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
