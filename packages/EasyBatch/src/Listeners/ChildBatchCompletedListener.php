<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Listeners;

use EonX\EasyBatch\Events\BatchCompletedEvent;
use EonX\EasyBatch\Events\BatchItemFailedEvent;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectApproverInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class ChildBatchCompletedListener
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface
     */
    private $batchItemRepository;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchObjectApproverInterface
     */
    private $batchObjectApprover;

    /**
     * @var \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(
        BatchItemRepositoryInterface $batchItemRepository,
        BatchObjectApproverInterface $batchObjectApprover,
        EventDispatcherInterface $dispatcher
    ) {
        $this->batchItemRepository = $batchItemRepository;
        $this->batchObjectApprover = $batchObjectApprover;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemNotFoundException
     */
    public function __invoke(BatchCompletedEvent $event): void
    {
        $batch = $event->getBatch();
        $batchItemId = $batch->getBatchItemId();

        // Batch does not belong to another batch
        if ($batchItemId === null) {
            return;
        }

        $batchItem = $this->batchItemRepository->findOrFail($batchItemId);

        // If batch successful, approval batchItem "placeholder" in parent batch
        if ($batch->getStatus() === BatchInterface::STATUS_SUCCESS) {
            $this->batchObjectApprover->approve($batchItem);

            return;
        }

        // Otherwise, fail batchItem "placeholder"
        $batchItem->setStatus(BatchItemInterface::STATUS_FAILED);

        $this->batchItemRepository->save($batchItem);

        $this->dispatcher->dispatch(new BatchItemFailedEvent($batchItem));
    }
}
