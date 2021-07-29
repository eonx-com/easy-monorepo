<?php

declare(strict_types=1);

namespace EonX\EasyBatch;

use Carbon\Carbon;
use EonX\EasyBatch\Events\BatchCancelledEvent;
use EonX\EasyBatch\Events\BatchCompletedEvent;
use EonX\EasyBatch\Events\BatchItemCancelledEvent;
use EonX\EasyBatch\Events\BatchItemCompletedEvent;
use EonX\EasyBatch\Exceptions\BatchCancelledException;
use EonX\EasyBatch\Exceptions\BatchItemCancelledException;
use EonX\EasyBatch\Exceptions\BatchObjectNotSupportedException;
use EonX\EasyBatch\Interfaces\AsyncDispatcherInterface;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchItemFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchManagerInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Objects\MessageDecorator;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyPagination\Data\StartSizeData;

final class BatchManager implements BatchManagerInterface
{
    /**
     * @var \EonX\EasyBatch\Interfaces\AsyncDispatcherInterface
     */
    private $asyncDispatcher;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchItemFactoryInterface
     */
    private $batchItemFactory;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface
     */
    private $batchItemRepository;

    /**
     * @var int
     */
    private $batchItemsPerPage;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchRepositoryInterface
     */
    private $batchRepository;

    /**
     * @var \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        AsyncDispatcherInterface $asyncDispatcher,
        BatchRepositoryInterface $batchRepository,
        BatchItemFactoryInterface $batchItemFactory,
        BatchItemRepositoryInterface $batchItemRepository,
        EventDispatcherInterface $eventDispatcher,
        ?int $batchItemsPerPage = null
    ) {
        $this->asyncDispatcher = $asyncDispatcher;
        $this->batchRepository = $batchRepository;
        $this->batchItemFactory = $batchItemFactory;
        $this->batchItemRepository = $batchItemRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->batchItemsPerPage = $batchItemsPerPage ?? self::DEFAULT_BATCH_ITEMS_PER_PAGE;
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectNotSupportedException
     */
    public function approve(BatchObjectInterface $batchObject): BatchObjectInterface
    {
        if ($batchObject->isCancelled() || $batchObject->isFailed()) {
            throw new BatchObjectNotSupportedException(\sprintf(
                'Cannot approve BatchObject of type "%s" with status "%s"',
                \get_class($batchObject),
                $batchObject->getStatus()
            ));
        }

        if ($batchObject->isSucceeded()) {
            return $batchObject;
        }

        $batchObject->setStatus(BatchObjectInterface::STATUS_SUCCEEDED);

        if ($batchObject instanceof BatchInterface) {
            return $this->updateBatch($batchObject);
        }

        if ($batchObject instanceof BatchItemInterface) {
            $batchItem = $this->updateItem($batchObject);

            $this->updateBatchForItem(
                $this->batchRepository->findOrFail($batchItem->getBatchId()),
                $batchItem
            );

            return $batchItem;
        }

        throw new BatchObjectNotSupportedException(\sprintf(
            'BatchObject of type "%s" not supported. Supported types: ["%s"]',
            \get_class($batchObject),
            \implode('", "', [BatchInterface::class, BatchItemInterface::class])
        ));
    }

    public function cancel(BatchObjectInterface $batchObject): BatchObjectInterface
    {
        $batchObject
            ->setCancelledAt(Carbon::now('UTC'))
            ->setStatus(BatchItemInterface::STATUS_CANCELLED);

        if ($batchObject instanceof BatchInterface) {
            return $this->updateBatch($batchObject);
        }

        if ($batchObject instanceof BatchItemInterface) {
            $batchItem = $this->updateItem($batchObject);

            /** @var int|string $batchItemId */
            $batchItemId = $batchItem->getId();
            $batch = $this->batchRepository->findOrFail($batchItem->getBatchId());

            $batch->setProcessed($batch->countProcessed() + 1);

            $this->updateBatch($batch);

            if ($batchItem->getType() === BatchItemInterface::TYPE_NESTED_BATCH) {
                $nestedBatch = $this->batchRepository->findNestedOrFail($batchItemId);

                $nestedBatch
                    ->setCancelledAt(Carbon::now('UTC'))
                    ->setStatus(BatchItemInterface::STATUS_CANCELLED);

                $this->updateBatch($nestedBatch);
            }

            return $batchItem;
        }

        throw new BatchObjectNotSupportedException(\sprintf(
            'BatchObject of type "%s" not supported. Supported types: ["%s"]',
            \get_class($batchObject),
            \implode('", "', [BatchInterface::class, BatchItemInterface::class])
        ));
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemInvalidException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    public function dispatch(BatchInterface $batch): BatchInterface
    {
        if ($batch->getId() === null) {
            $batch = $this->persistBatchRecursive($batch);
        }

        $this->asyncDispatcher->dispatch($batch);

        return $batch;
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemInvalidException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    public function dispatchItem(BatchItemInterface $batchItem): BatchItemInterface
    {
        $this->asyncDispatcher->dispatch($batchItem);

        return $batchItem;
    }

    /**
     * @param int|string $batchId
     */
    public function iterateThroughItems($batchId, ?string $dependsOnName, callable $func): void
    {
        $page = 1;

        do {
            $paginator = $this->batchItemRepository->findForDispatch(
                new StartSizeData($page, $this->batchItemsPerPage),
                $batchId,
                $dependsOnName
            );

            foreach ($paginator->getItems() as $item) {
                \call_user_func($func, $item);
            }

            $page++;
        } while ($paginator->hasNextPage());
    }

    /**
     * @return mixed
     *
     * @throws \EonX\EasyBatch\Exceptions\BatchCancelledException
     * @throws \EonX\EasyBatch\Exceptions\BatchItemCancelledException
     * @throws \Throwable
     */
    public function processItem(BatchInterface $batch, BatchItemInterface $batchItem, callable $func)
    {
        if ($batchItem->isCancelled()) {
            throw new BatchItemCancelledException(\sprintf('BatchItem "%s" is cancelled', $batchItem->getId()));
        }

        if ($batch->isCancelled()) {
            $throwable = new BatchCancelledException(\sprintf('Batch for id "%s" is cancelled', $batch->getId()));

            $batchItem->setThrowable($throwable);

            $this->cancel($batchItem);

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

            $this->updateBatchForItem($batch, $this->updateItem($batchItem));
        }
    }

    private function dispatchBatchEvents(BatchInterface $batch): void
    {
        if ($batch->isCancelled()) {
            $this->eventDispatcher->dispatch(new BatchCancelledEvent($batch));
        }

        if ($batch->isCompleted()) {
            $this->eventDispatcher->dispatch(new BatchCompletedEvent($batch));
        }
    }

    private function dispatchBatchItemEvents(BatchItemInterface $batchItem): void
    {
        if ($batchItem->isCancelled()) {
            $this->eventDispatcher->dispatch(new BatchItemCancelledEvent($batchItem));
        }

        if ($batchItem->isCompleted()) {
            $this->eventDispatcher->dispatch(new BatchItemCompletedEvent($batchItem));
        }
    }

    /**
     * @param int|string $batchId
     */
    private function persistBatchItem($batchId, MessageDecorator $item, ?object $message = null): BatchItemInterface
    {
        $batchItem = $this->batchItemFactory->create($batchId, $message, $item->getClass());

        $batchItem->setApprovalRequired($item->isApprovalRequired());

        if ($item->getDependsOn() !== null) {
            $batchItem->setDependsOnName($item->getDependsOn());
        }

        if ($item->getMetadata() !== null) {
            $batchItem->setMetadata($item->getMetadata());
        }

        if ($item->getName() !== null) {
            $batchItem->setName($item->getName());
        }

        return $this->batchItemRepository->save($batchItem);
    }

    private function persistBatchRecursive(BatchInterface $batch): BatchInterface
    {
        $batch = $this->batchRepository->save($batch);
        /** @var int|string $batchId */
        $batchId = $batch->getId();
        $totalItems = 0;

        foreach ($batch->getItems() as $item) {
            $totalItems++;

            $item = MessageDecorator::wrap($item);
            $message = $item->getMessage();

            if ($message instanceof BatchInterface) {
                /** @var int|string $batchItemId */
                $batchItemId = $this->persistBatchItem($batchId, $item)->getId();

                $message->setParentBatchItemId($batchItemId);

                $this->persistBatchRecursive($message);

                continue;
            }

            $this->persistBatchItem($batchId, $item, $message);
        }

        $batch->setTotal($totalItems);

        return $this->batchRepository->save($batch);
    }

    private function updateBatch(BatchInterface $batch): BatchInterface
    {
        $batch = $this->batchRepository->save($batch);

        $this->dispatchBatchEvents($batch);

        return $batch;
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    private function updateBatchForItem(BatchInterface $batch, BatchItemInterface $batchItem): void
    {
        $update = function (BatchInterface $freshBatch) use ($batchItem): BatchInterface {
            // Update processed only on first attempt and/or if not pending approval
            if ($batchItem->isRetried() === false && $batchItem->isCompleted()) {
                $freshBatch->setProcessed($freshBatch->countProcessed() + 1);
            }

            // Forget about previous fail, and see what happens this time
            if ($batchItem->isRetried()) {
                $freshBatch->setFailed($freshBatch->countFailed() - 1);
            }

            if ($batchItem->isFailed()) {
                $freshBatch->setFailed($freshBatch->countFailed() + 1);
            }

            if ($batchItem->isSucceeded()) {
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
                    $freshBatch->countFailed() > 0 ? BatchInterface::STATUS_FAILED : BatchInterface::STATUS_SUCCEEDED
                );
            }

            // Handle previously completed batch
            if ($freshBatch->isCompleted() === false && $freshBatch->countProcessed() > 0) {
                $freshBatch->setStatus(BatchInterface::STATUS_PROCESSING);
            }

            return $freshBatch;
        };

        $batch = $this->batchRepository->updateAtomic($batch, $update);

        $this->dispatchBatchEvents($batch);
    }

    private function updateItem(BatchItemInterface $batchItem): BatchItemInterface
    {
        $batchItem = $this->batchItemRepository->save($batchItem);

        $this->dispatchBatchItemEvents($batchItem);

        return $batchItem;
    }
}
