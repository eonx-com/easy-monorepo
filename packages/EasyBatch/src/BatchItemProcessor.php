<?php

declare(strict_types=1);

namespace EonX\EasyBatch;

use Carbon\Carbon;
use EonX\EasyBatch\Exceptions\BatchCancelledException;
use EonX\EasyBatch\Exceptions\BatchNotFoundException;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemProcessorInterface;
use EonX\EasyBatch\Interfaces\BatchItemUpdaterInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchUpdaterInterface;

final class BatchItemProcessor implements BatchItemProcessorInterface
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchRepositoryInterface
     */
    private $batchRepository;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchItemUpdaterInterface
     */
    private $batchItemUpdater;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchUpdaterInterface
     */
    private $batchUpdater;

    public function __construct(
        BatchItemUpdaterInterface $batchItemUpdater,
        BatchUpdaterInterface $batchUpdater,
        BatchRepositoryInterface $batchRepository
    ) {
        $this->batchItemUpdater = $batchItemUpdater;
        $this->batchUpdater = $batchUpdater;
        $this->batchRepository = $batchRepository;
    }

    /**
     * @return mixed The return from $func
     *
     * @throws \EonX\EasyBatch\Exceptions\BatchCancelledException
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     * @throws \Throwable
     */
    public function process(BatchItemInterface $batchItem, callable $func)
    {
        $batch = $this->batchRepository->find($batchItem->getBatchId());

        // Batch not found
        if ($batch === null) {
            $batchItem->setStatus(BatchItemInterface::STATUS_FAILED);
            $throwable = new BatchNotFoundException(\sprintf('Batch for id "%s" not found', $batchItem->getBatchId()));

            $this->batchItemUpdater->updateNotProcessed($batchItem, $throwable);

            throw $throwable;
        }

        // Batch cancelled
        if ($batch->getStatus() === BatchInterface::STATUS_CANCELLED) {
            $batchItem->setStatus(BatchItemInterface::STATUS_CANCELLED);
            $throwable = new BatchCancelledException(\sprintf('Batch for id "%s" is cancelled', $batch->getId()));

            $this->batchItemUpdater->updateNotProcessed($batchItem, $throwable);

            throw $throwable;
        }

        try {
            $batchItem->setAttempts($batchItem->getAttempts() + 1);
            $batchItem->setStartedAt(Carbon::now('UTC'));

            $result = $func();

            $batchItem->setStatus(
                $batchItem->isApprovalRequired()
                    ? BatchItemInterface::STATUS_SUCCEEDED_PENDING_APPROVAL
                    : BatchItemInterface::STATUS_SUCCEEDED
            );

            return $result;
        } catch (\Throwable $throwable) {
            $batchItem->setStatus(BatchItemInterface::STATUS_FAILED);
            $batchItem->setThrowable($throwable);

            throw $throwable;
        } finally {
            $batchItem->setFinishedAt(Carbon::now('UTC'));

            $this->batchItemUpdater->update($batchItem);
            $this->batchUpdater->updateForItem($batch, $batchItem);
        }
    }
}
