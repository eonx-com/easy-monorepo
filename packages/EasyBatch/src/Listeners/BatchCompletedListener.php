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
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectNotSupportedException
     */
    public function __invoke(BatchCompletedEvent $event): void
    {
        /** @var null|int|string $parentBatchItemId */
        $parentBatchItemId = $event->getBatch()->getParentBatchItemId();

        if ($parentBatchItemId === null) {
            return;
        }

        $parentBatchItem = $this->batchItemRepository->findOrFail($parentBatchItemId);

        $this->batchManager->approve($parentBatchItem);
    }
}
