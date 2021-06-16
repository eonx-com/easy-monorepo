<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Listeners;

use EonX\EasyBatch\Events\BatchCompletedEvent;
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
     * @var \EonX\EasyBatch\Interfaces\BatchItemUpdaterInterface
     */
    private $batchItemUpdater;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchRepositoryInterface
     */
    private $batchRepository;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchUpdaterInterface
     */
    private $batchUpdater;

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
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     */
    public function __invoke(BatchCompletedEvent $event): void
    {
        $batch = $event->getBatch();
        $batchItemId = $batch->getParentBatchItemId();

        // Batch does not belong to another batch
        if ($batchItemId === null) {
            return;
        }

        // Update batchItem and parent batch

        $batchItem = $this->batchItemRepository->findOrFail($batchItemId);
        $parentBatch = $this->batchRepository->findOrFail($batchItem->getBatchId());

        $batchItem->setStatus($batch->getStatus());

        $batchItem = $this->batchItemUpdater->update($batchItem);

        $this->batchUpdater->updateForItem($parentBatch, $batchItem);
    }
}
