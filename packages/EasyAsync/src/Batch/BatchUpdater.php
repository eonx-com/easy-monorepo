<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Batch;

use Carbon\Carbon;
use EonX\EasyAsync\Events\Batch\BatchCompletedEvent;
use EonX\EasyAsync\Exceptions\Batch\BatchIdRequiredException;
use EonX\EasyAsync\Interfaces\Batch\BatchInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchItemInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchStoreInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchUpdaterInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class BatchUpdater implements BatchUpdaterInterface
{
    /**
     * @var \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var \EonX\EasyAsync\Interfaces\Batch\BatchStoreInterface
     */
    private $store;

    public function __construct(EventDispatcherInterface $dispatcher, BatchStoreInterface $store)
    {
        $this->dispatcher = $dispatcher;
        $this->store = $store;
    }

    /**
     * @throws \EonX\EasyAsync\Exceptions\Batch\BatchIdRequiredException
     * @throws \EonX\EasyAsync\Exceptions\Batch\BatchNotFoundException
     * @throws \Throwable
     */
    public function updateForItem(BatchInterface $batch, BatchItemInterface $batchItem): BatchInterface
    {
        if ($batch->getId() === null) {
            throw new BatchIdRequiredException('Batch ID is required to store it.');
        }

        $this->store->startUpdate();

        try {
            $freshBatch = $this->store->findForUpdate($batch->getId());

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

            $freshBatch = $this->store->update($freshBatch);

            $this->store->finishUpdate();

            // Dispatch completed event if needed
            if ($freshBatch->isCompleted()) {
                $this->dispatcher->dispatch(new BatchCompletedEvent($freshBatch));
            }

            return $freshBatch;
        } catch (\Throwable $throwable) {
            $this->store->cancelUpdate();

            throw $throwable;
        }
    }

    private function shouldUpdateProcessedCount(BatchItemInterface $batchItem): bool
    {
        // Update processed only on first attempt and/or if not pending approval
        return $batchItem->isRetried() === false
            && $batchItem->getStatus() !== BatchItemInterface::STATUS_SUCCESS_PENDING_APPROVAL;
    }
}
