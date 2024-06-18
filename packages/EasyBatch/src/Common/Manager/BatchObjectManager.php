<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Manager;

use Carbon\Carbon;
use Closure;
use EonX\EasyBatch\Common\Dispatcher\BatchItemDispatcher;
use EonX\EasyBatch\Common\Event\BatchCompletedEvent;
use EonX\EasyBatch\Common\Exception\BatchObjectInvalidException;
use EonX\EasyBatch\Common\Exception\BatchObjectNotSupportedException;
use EonX\EasyBatch\Common\Iterator\BatchItemIteratorInterface;
use EonX\EasyBatch\Common\Persister\BatchPersister;
use EonX\EasyBatch\Common\Processor\BatchProcessor;
use EonX\EasyBatch\Common\Repository\BatchItemRepositoryInterface;
use EonX\EasyBatch\Common\Repository\BatchRepositoryInterface;
use EonX\EasyBatch\Common\ValueObject\BatchInterface;
use EonX\EasyBatch\Common\ValueObject\BatchItemInterface;
use EonX\EasyBatch\Common\ValueObject\BatchItemIteratorConfig;
use EonX\EasyBatch\Common\ValueObject\BatchObjectInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class BatchObjectManager implements BatchObjectManagerInterface
{
    public function __construct(
        private readonly BatchPersister $batchPersister,
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly BatchProcessor $batchProcessor,
        private readonly BatchItemDispatcher $batchItemDispatcher,
        private readonly BatchItemIteratorInterface $batchItemIterator,
        private readonly BatchItemRepositoryInterface $batchItemRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemNotFoundException
     * @throws \EonX\EasyBatch\Common\Exception\BatchNotFoundException
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectNotSupportedException
     */
    public function approve(BatchObjectInterface $batchObject): BatchObjectInterface
    {
        if ($batchObject->isSucceeded()) {
            return $batchObject;
        }

        if ($batchObject->isPendingApproval() === false) {
            throw new BatchObjectNotSupportedException(\sprintf(
                'Cannot approve BatchObject of type "%s" with status "%s"',
                $batchObject::class,
                $batchObject->getStatus()
            ));
        }

        $batchObject->setStatus(BatchObjectInterface::STATUS_SUCCEEDED);

        if ($batchObject->getStartedAt() === null) {
            $batchObject->setStartedAt(Carbon::now('UTC'));
        }

        if ($batchObject->getFinishedAt() === null) {
            $batchObject->setFinishedAt(Carbon::now('UTC'));
        }

        // Batch
        if ($batchObject instanceof BatchInterface) {
            $this->batchRepository->save($batchObject);

            // If nested batch, approve parent batchItem
            if ($batchObject->getParentBatchItemId() !== null) {
                $this->approve($this->batchItemRepository->findOrFail($batchObject->getParentBatchItemId()));
            }

            // Approving a batch is possible only when all its batchItems succeeded and approval was required
            // Which means there is nothing else to do to it, except dispatching its completed event
            $this->eventDispatcher->dispatch(new BatchCompletedEvent($batchObject));

            return $batchObject;
        }

        // BatchItem
        if ($batchObject instanceof BatchItemInterface) {
            if ($batchObject->getAttempts() === 0) {
                $batchObject->setAttempts(1);
            }

            if ($batchObject->getStatus() !== BatchItemInterface::STATUS_PROCESSING_DEPENDENT_OBJECTS) {
                $this->batchItemRepository->save($batchObject);
            }

            // Dispatch dependent batchItems
            if ($batchObject->getName() !== null) {
                $this->batchItemDispatcher->dispatchDependItems($this, $batchObject);
            }

            // Approve nested batch
            if ($batchObject->getType() === BatchItemInterface::TYPE_NESTED_BATCH) {
                $this->approve($this->batchRepository->findNestedOrFail($batchObject->getIdOrFail()));
            }

            // Update batch for batchItem
            $this->batchProcessor->processBatchForBatchItem(
                $this,
                $this->batchRepository->findOrFail($batchObject->getBatchId()),
                $batchObject
            );

            return $batchObject;
        }

        throw new BatchObjectNotSupportedException(\sprintf(
            'BatchObject of type "%s" not supported. Supported types: ["%s"]',
            $batchObject::class,
            \implode('", "', [BatchInterface::class, BatchItemInterface::class])
        ));
    }

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemNotFoundException
     * @throws \EonX\EasyBatch\Common\Exception\BatchNotFoundException
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectInvalidException
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectNotSupportedException
     */
    public function cancel(BatchObjectInterface $batchObject): BatchObjectInterface
    {
        if ($batchObject->isCancelled()) {
            return $batchObject;
        }

        if ($batchObject->isCompleted()) {
            throw new BatchObjectInvalidException(\sprintf(
                'Cannot cancel already completed batch object "%s"',
                $batchObject->getId()
            ));
        }

        $batchObject
            ->setCancelledAt(Carbon::now('UTC'))
            ->setStatus(BatchObjectInterface::STATUS_CANCELLED);

        // Batch
        if ($batchObject instanceof BatchInterface) {
            $this->batchRepository->save($batchObject);

            // Cancel remaining batchItems
            $iteratorConfig = BatchItemIteratorConfig::create(
                $batchObject->getIdOrFail(),
                $this->getCancelBatchItemClosure()
            )
                ->forCancel();
            $this->batchItemIterator->iterateThroughItems($iteratorConfig);

            // If nested batch, cancel parent batchItem
            if ($batchObject->getParentBatchItemId() !== null) {
                $this->cancel($this->batchItemRepository->findOrFail($batchObject->getParentBatchItemId()));
            }

            return $batchObject;
        }

        // BatchItem
        if ($batchObject instanceof BatchItemInterface) {
            if ($batchObject->getAttempts() === 0) {
                $batchObject->setAttempts(1);
            }

            if ($batchObject->getStatus() !== BatchItemInterface::STATUS_PROCESSING_DEPENDENT_OBJECTS) {
                $this->batchItemRepository->save($batchObject);
            }

            // Cancel dependent batchItems
            if ($batchObject->getName() !== null) {
                $iteratorConfig = BatchItemIteratorConfig::create(
                    $batchObject->getBatchId(),
                    $this->getCancelBatchItemClosure(),
                    $batchObject->getName()
                )->forCancel();

                $this->batchItemIterator->iterateThroughItems($iteratorConfig);
            }

            // Cancel nested batch
            if ($batchObject->getType() === BatchItemInterface::TYPE_NESTED_BATCH) {
                $this->cancel($this->batchRepository->findNestedOrFail($batchObject->getIdOrFail()));
            }

            // Update batch for batchItem
            $this->batchProcessor->processBatchForBatchItem(
                $this,
                $this->batchRepository->findOrFail($batchObject->getBatchId()),
                $batchObject
            );

            return $batchObject;
        }

        throw new BatchObjectNotSupportedException(\sprintf(
            'BatchObject of type "%s" not supported. Supported types: ["%s"]',
            $batchObject::class,
            \implode('", "', [BatchInterface::class, BatchItemInterface::class])
        ));
    }

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemNotFoundException
     * @throws \EonX\EasyBatch\Common\Exception\BatchNotFoundException
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectNotSupportedException
     */
    public function dispatchBatch(BatchInterface $batch, ?callable $beforeFirstDispatch = null): BatchInterface
    {
        if ($batch->getId() === null) {
            $batch = $this->batchPersister->persistBatch($batch);

            if ($beforeFirstDispatch !== null) {
                $beforeFirstDispatch($batch);
            }
        }

        // Dispatch each item individually
        $this->batchItemDispatcher->dispatchItemsForBatch($this, $batch);

        // Allow to dispatch a batch with no item, and trigger all completed logic as expected
        if ($batch->countTotal() === 0) {
            // Explicitly set the batch status to pending approval
            $batch->setStatus(BatchObjectInterface::STATUS_SUCCEEDED_PENDING_APPROVAL);

            /** @var \EonX\EasyBatch\Common\ValueObject\BatchInterface $batch */
            $batch = $this->approve($batch);
        }

        return $batch;
    }

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemNotFoundException
     * @throws \EonX\EasyBatch\Common\Exception\BatchNotFoundException
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
     */
    public function restoreBatchState(int|string $batchId): BatchInterface
    {
        return $this->batchProcessor->restoreState(
            $this,
            $this->batchRepository->findOrFail($batchId)
        );
    }

    private function getCancelBatchItemClosure(): Closure
    {
        return function (BatchItemInterface $batchItem): void {
            $this->cancel($batchItem);
        };
    }
}
