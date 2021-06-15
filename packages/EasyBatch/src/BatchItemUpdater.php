<?php

declare(strict_types=1);

namespace EonX\EasyBatch;

use EonX\EasyBatch\Events\BatchItemCompletedEvent;
use EonX\EasyBatch\Events\BatchItemNotProcessedEvent;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemUpdaterInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class BatchItemUpdater implements BatchItemUpdaterInterface
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface
     */
    private $batchItemRepository;

    /**
     * @var \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(BatchItemRepositoryInterface $batchItemRepository, EventDispatcherInterface $dispatcher)
    {
        $this->batchItemRepository = $batchItemRepository;
        $this->dispatcher = $dispatcher;
    }

    public function update(BatchItemInterface $batchItem): BatchItemInterface
    {
        if ($batchItem->isCompleted()) {
            $event = new BatchItemCompletedEvent($batchItem);

            $this->dispatcher->dispatch($event);

            $batchItem = $event->getBatchItem();
        }

        return $this->batchItemRepository->save($batchItem);
    }

    public function updateNotProcessed(BatchItemInterface $batchItem, ?\Throwable $throwable = null): BatchItemInterface
    {
        if ($throwable !== null) {
            $batchItem->setThrowable($throwable);
        }

        $event = new BatchItemNotProcessedEvent($batchItem, $throwable);

        $this->dispatcher->dispatch($event);

        return $this->batchItemRepository->save($event->getBatchItem());
    }
}
