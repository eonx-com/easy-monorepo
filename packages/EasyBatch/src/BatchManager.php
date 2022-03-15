<?php

declare(strict_types=1);

namespace EonX\EasyBatch;

use Carbon\Carbon;
use EonX\EasyBatch\Events\BatchCancelledEvent;
use EonX\EasyBatch\Events\BatchCompletedEvent;
use EonX\EasyBatch\Events\BatchItemCancelledEvent;
use EonX\EasyBatch\Events\BatchItemCompletedEvent;
use EonX\EasyBatch\Exceptions\BatchCancelledException;
use EonX\EasyBatch\Exceptions\BatchItemCannotBeRetriedException;
use EonX\EasyBatch\Exceptions\BatchItemCompletedException;
use EonX\EasyBatch\Exceptions\BatchItemInvalidException;
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
use EonX\EasyPagination\Pagination;

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

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectNotSupportedException
     */
    public function cancel(BatchObjectInterface $batchObject): BatchObjectInterface
    {
        if ($batchObject->isCancelled()) {
            return $batchObject;
        }

        $batchObject
            ->setCancelledAt(Carbon::now('UTC'))
            ->setStatus(BatchObjectInterface::STATUS_CANCELLED);

        if ($batchObject instanceof BatchInterface) {
            return $this->updateBatch($batchObject);
        }

        if ($batchObject instanceof BatchItemInterface) {
            $batchItem = $this->updateItem($batchObject);

            /** @var int|string $batchItemId */
            $batchItemId = $batchItem->getId();
            $batch = $this->batchRepository->findOrFail($batchItem->getBatchId());

            $this->updateBatchForItem($batch, $batchItem);

            if ($batchItem->getType() === BatchItemInterface::TYPE_NESTED_BATCH) {
                $this->cancel($this->batchRepository->findNestedOrFail($batchItemId));
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
    public function dispatch(BatchInterface $batch, ?callable $beforeFirstDispatch = null): BatchInterface
    {
        if ($batch->getId() === null) {
            $batch = $this->persistBatchRecursive($batch);

            if ($beforeFirstDispatch !== null) {
                $beforeFirstDispatch($batch);
            }
        }

        /** @var int|string $batchId */
        $batchId = $batch->getId();

        $this->iterateThroughItems($batchId, function (BatchItemInterface $batchItem): void {
            if ($batchItem->getType() === BatchItemInterface::TYPE_MESSAGE) {
                $this->asyncDispatcher->dispatchItem($batchItem);

                return;
            }

            if ($batchItem->getType() === BatchItemInterface::TYPE_NESTED_BATCH) {
                /** @var int|string $batchItemId */
                $batchItemId = $batchItem->getId();
                $this->dispatch($this->batchRepository->findNestedOrFail($batchItemId));
            }
        });

        return $batch;
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemInvalidException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    public function dispatchItem(BatchItemInterface $batchItem): BatchItemInterface
    {
        $this->asyncDispatcher->dispatchItem($batchItem);

        return $batchItem;
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemInvalidException
     */
    public function failItem(BatchItemInterface $batchItem): BatchItemInterface
    {
        if ($batchItem->isSucceeded()) {
            throw new BatchItemInvalidException(\sprintf('BatchItem "%s" already succeeded', $batchItem->getId()));
        }

        if ($batchItem->isCancelled() || $batchItem->isFailed()) {
            return $batchItem;
        }

        $batchItem
            ->setStatus(BatchObjectInterface::STATUS_FAILED)
            ->setFinishedAt(Carbon::now('UTC'));

        return $this->updateItem($batchItem);
    }

    public function iterateThroughItems(int|string $batchId, callable $func, ?string $dependsOnName = null): void
    {
        $page = 1;
        $pagesCache = [];

        do {
            $paginator = $this->batchItemRepository->findForDispatch(
                new Pagination($page, $this->batchItemsPerPage),
                $batchId,
                $dependsOnName
            );

            /** @var \EonX\EasyBatch\Interfaces\BatchItemInterface[] $items */
            $items = $paginator->getItems();

            // Check hasNextPage before iterating through items in case the logic modifies the pagination,
            // It would impact the total number of pages as well.
            $hasNextPage = $paginator->hasNextPage();

            // Implement hash and cache mechanism to prevent infinite loop due to resetPagination logic.
            // resetPagination should apply only when pagination actually changed
            $pageHash = $this->generateItemPageHash($page, $items);
            $pageAlreadyProcessed = isset($pagesCache[$pageHash]);
            $pagesCache[$pageHash] = true;

            if ($pageAlreadyProcessed === false) {
                foreach ($items as $item) {
                    \call_user_func($func, $item, $this->batchItemRepository);
                }
            }

            // Since pagination is based on status, it gets modified as items are processed
            // which results in missing items to dispatch. The solution is to reset the pagination until all items
            // have been dispatched as expected.
            // Reset pagination only when not on first page, and last page was reached.
            // Because no more items to dispatch means 0 items in first page.
            $resetPagination = $page > 1 && $hasNextPage === false;
            $page = $resetPagination ? 1 : $page + 1;
        } while (($hasNextPage || $resetPagination) && $pageAlreadyProcessed === false);
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchCancelledException
     * @throws \Throwable
     */
    public function processItem(BatchInterface $batch, BatchItemInterface $batchItem, callable $func): mixed
    {
        if ($batchItem->isCompleted()) {
            throw new BatchItemCompletedException(\sprintf(
                'BatchItem "%s" is already completed with status "%s"',
                $batchItem->getId(),
                $batchItem->getStatus()
            ));
        }

        if ($batchItem->canBeRetried() === false) {
            throw new BatchItemCannotBeRetriedException(\sprintf(
                'BatchItem "%s" cannot be retried because current attempt %d is not lower than max attempts %d',
                $batchItem->getId(),
                $batchItem->getAttempts(),
                $batchItem->getMaxAttempts()
            ));
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
                    ? BatchObjectInterface::STATUS_SUCCEEDED_PENDING_APPROVAL
                    : BatchObjectInterface::STATUS_SUCCEEDED
            );

            return $result;
        } catch (\Throwable $throwable) {
            $batchItem->setStatus(
                $batchItem->canBeRetried()
                    ? BatchItemInterface::STATUS_FAILED_PENDING_RETRY
                    : BatchObjectInterface::STATUS_FAILED
            );
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

    private function persistBatchItem(
        int|string $batchId,
        MessageDecorator $item,
        ?object $message = null
    ): BatchItemInterface {
        $batchItem = $this->batchItemFactory->create($batchId, $message, $item->getClass());

        $batchItem
            ->setApprovalRequired($item->isApprovalRequired())
            ->setMaxAttempts($item->getMaxAttempts());

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
                $batchItemId = $this->persistBatchItem($batchId, $item)
                    ->getId();

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
            if ($batchItem->isCompleted()) {
                $freshBatch->setProcessed($freshBatch->countProcessed() + 1);
            }

            if ($batchItem->isCancelled()) {
                $freshBatch->setCancelled($freshBatch->countCancelled() + 1);
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
                $freshBatch->setStatus(BatchObjectInterface::STATUS_PROCESSING);
            }

            // Last item of the batch
            if ($freshBatch->countTotal() === $freshBatch->countProcessed()) {
                $freshBatch->setFinishedAt(Carbon::now('UTC'));

                // All items are cancelled, cancel batch
                if ($freshBatch->countCancelled() === $freshBatch->countTotal()) {
                    $freshBatch->setStatus(BatchObjectInterface::STATUS_CANCELLED);
                }

                // If batch not cancelled from statement above, set status
                if ($freshBatch->isCancelled() === false) {
                    // Batch failed if not all items succeeded
                    $freshBatch->setStatus(
                        $freshBatch->countSucceeded() < $freshBatch->countTotal()
                            ? BatchObjectInterface::STATUS_FAILED
                            : BatchObjectInterface::STATUS_SUCCEEDED
                    );
                }
            }

            // Handle previously completed batch
            if ($freshBatch->isCompleted() === false && $freshBatch->countProcessed() > 0) {
                $freshBatch->setStatus(BatchObjectInterface::STATUS_PROCESSING);
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

    /**
     * @param \EonX\EasyBatch\Interfaces\BatchItemInterface[] $batchItems
     */
    private function generateItemPageHash(int $page, array $batchItems): string
    {
        $hash = (string)$page;

        foreach ($batchItems as $batchItem) {
            $hash .= (string)$batchItem->getId();
        }

        return \md5($hash);
    }
}
