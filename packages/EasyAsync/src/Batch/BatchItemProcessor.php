<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Batch;

use Carbon\Carbon;
use EonX\EasyAsync\Events\Batch\BatchItemFailedEvent;
use EonX\EasyAsync\Events\Batch\BatchItemNotProcessedEvent;
use EonX\EasyAsync\Events\Batch\BatchItemSuccessEvent;
use EonX\EasyAsync\Exceptions\Batch\BatchCancelledException;
use EonX\EasyAsync\Exceptions\Batch\BatchNotFoundException;
use EonX\EasyAsync\Interfaces\Batch\BatchInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchItemInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchItemProcessorInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchItemStoreInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchStoreInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class BatchItemProcessor implements BatchItemProcessorInterface
{
    /**
     * @var \EonX\EasyAsync\Interfaces\Batch\BatchItemStoreInterface
     */
    private $batchItemStore;

    /**
     * @var \EonX\EasyAsync\Interfaces\Batch\BatchStoreInterface
     */
    private $batchStore;

    /**
     * @var \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(
        BatchStoreInterface $batchStore,
        BatchItemStoreInterface $batchItemStore,
        EventDispatcherInterface $dispatcher
    ) {
        $this->batchStore = $batchStore;
        $this->batchItemStore = $batchItemStore;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return mixed The return from $func
     *
     * @throws \EonX\EasyAsync\Exceptions\Batch\BatchCancelledException
     * @throws \EonX\EasyAsync\Exceptions\Batch\BatchNotFoundException
     * @throws \Throwable
     */
    public function process(BatchItemInterface $batchItem, callable $func)
    {
        $batch = $this->batchStore->find($batchItem->getBatchId());

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

            $batchItem->setStatus(BatchItemInterface::STATUS_SUCCESS);

            $this->dispatcher->dispatch(new BatchItemSuccessEvent($batchItem));

            return $result;
        } catch (\Throwable $throwable) {
            $batchItem->setStatus(BatchItemInterface::STATUS_FAILED);
            $batchItem->setThrowable($throwable);

            $this->dispatcher->dispatch(new BatchItemFailedEvent($batchItem));

            throw $throwable;
        } finally {
            $batchItem->setFinishedAt(Carbon::now('UTC'));

            $this->batchItemStore->store($batchItem);
            $this->batchStore->updateForItem($batch, $batchItem);
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
        $this->batchItemStore->store($batchItem);

        return $throwable;
    }
}
