<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Listeners;

use EonX\EasyBatch\Events\BatchCompletedEvent;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchManagerInterface;

final class BatchCompletedListener
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface
     */
    private $batchItemRepository;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchManagerInterface
     */
    private $batchManager;

    public function __construct(BatchItemRepositoryInterface $batchItemRepository, BatchManagerInterface $batchManager)
    {
        $this->batchItemRepository = $batchItemRepository;
        $this->batchManager = $batchManager;
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemNotFoundException
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectNotSupportedException
     */
    public function __invoke(BatchCompletedEvent $event): void
    {
        $batch = $event->getBatch();
        /** @var null|int|string $parentBatchItemId */
        $parentBatchItemId = $batch->getParentBatchItemId();

        if ($parentBatchItemId === null) {
            return;
        }

        $parentBatchItem = $this->batchItemRepository->findOrFail($parentBatchItemId);

        if ($batch->isCancelled()) {
            $this->batchManager->cancel($parentBatchItem);
        }

        if ($batch->isFailed()) {
            $this->batchManager->failItem($parentBatchItem);
        }

        if ($batch->isSucceeded()) {
            $this->batchManager->approve($parentBatchItem);
        }
    }
}
