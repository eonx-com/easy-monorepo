<?php

declare(strict_types=1);

namespace EonX\EasyBatch;

use Carbon\Carbon;
use EonX\EasyBatch\Events\BatchItemFailedEvent;
use EonX\EasyBatch\Events\BatchItemNotProcessedEvent;
use EonX\EasyBatch\Events\BatchItemSuccessEvent;
use EonX\EasyBatch\Exceptions\BatchCancelledException;
use EonX\EasyBatch\Exceptions\BatchNotFoundException;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemProcessorInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchUpdaterInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class BatchItemProcessor implements BatchItemProcessorInterface
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface
     */
    private $batchItemRepository;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchRepositoryInterface
     */
    private $batchRepository;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchUpdaterInterface
     */
    private $batchUpdater;

    /**
     * @var \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(
        BatchUpdaterInterface $batchUpdater,
        BatchRepositoryInterface $batchRepository,
        BatchItemRepositoryInterface $batchItemRepository,
        EventDispatcherInterface $dispatcher
    ) {
        $this->batchUpdater = $batchUpdater;
        $this->batchRepository = $batchRepository;
        $this->batchItemRepository = $batchItemRepository;
        $this->dispatcher = $dispatcher;
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
            throw $this->doNotProcessBatchItemAndThrow(
                $batchItem,
                new BatchNotFoundException(\sprintf('Batch for id "%s" not found', $batchItem->getBatchId())),
                BatchItemInterface::STATUS_FAILED
            );
        }

        // Batch cancelled
        if ($batch->getStatus() === BatchInterface::STATUS_CANCELLED) {
            throw $this->doNotProcessBatchItemAndThrow(
                $batchItem,
                new BatchCancelledException(\sprintf('Batch for id "%s" is cancelled', $batch->getId())),
                BatchItemInterface::STATUS_CANCELLED
            );
        }

        try {
            $batchItem->setAttempts($batchItem->getAttempts() + 1);
            $batchItem->setStartedAt(Carbon::now('UTC'));

            $result = $func();

            $batchItem->setStatus(
                $batchItem->isApprovalRequired()
                    ? BatchItemInterface::STATUS_SUCCESS_PENDING_APPROVAL
                    : BatchItemInterface::STATUS_SUCCESS
            );

            $this->dispatcher->dispatch(new BatchItemSuccessEvent($batchItem));

            return $result;
        } catch (\Throwable $throwable) {
            $batchItem->setStatus(BatchItemInterface::STATUS_FAILED);
            $batchItem->setThrowable($throwable);

            $this->dispatcher->dispatch(new BatchItemFailedEvent($batchItem));

            throw $throwable;
        } finally {
            $batchItem->setFinishedAt(Carbon::now('UTC'));

            $this->batchItemRepository->save($batchItem);
            $this->batchUpdater->updateForItem($batch, $batchItem);
        }
    }

    private function doNotProcessBatchItemAndThrow(
        BatchItemInterface $batchItem,
        \Throwable $throwable,
        string $status
    ): \Throwable {
        $batchItem->setStatus($status);
        $batchItem->setThrowable($throwable);

        $this->dispatcher->dispatch(new BatchItemNotProcessedEvent($batchItem));
        $this->batchItemRepository->save($batchItem);

        return $throwable;
    }
}
