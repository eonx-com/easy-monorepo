<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Processor;

use Carbon\Carbon;
use EonX\EasyBatch\Common\Exception\BatchCancelledException;
use EonX\EasyBatch\Common\Exception\BatchItemCannotBeRetriedException;
use EonX\EasyBatch\Common\Exception\BatchItemCompletedException;
use EonX\EasyBatch\Common\Exception\BatchItemProcessedButNotSavedException;
use EonX\EasyBatch\Common\Exception\BatchItemSavedButBatchNotProcessedException;
use EonX\EasyBatch\Common\Manager\BatchObjectManagerInterface;
use EonX\EasyBatch\Common\Repository\BatchItemRepositoryInterface;
use EonX\EasyBatch\Common\ValueObject\BatchInterface;
use EonX\EasyBatch\Common\ValueObject\BatchItemInterface;
use EonX\EasyBatch\Common\ValueObject\BatchObjectInterface;
use Throwable;

final readonly class BatchItemProcessor
{
    public function __construct(
        private BatchProcessor $batchProcessor,
        private BatchItemRepositoryInterface $batchItemRepository,
        private BatchObjectManagerInterface $batchObjectManager,
    ) {
    }

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchCancelledException
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemCannotBeRetriedException
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemCompletedException
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemProcessedButNotSavedException
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemSavedButBatchNotProcessedException
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
     * @throws \Throwable
     */
    public function processBatchItem(BatchInterface $batch, BatchItemInterface $batchItem, callable $func): mixed
    {
        // If process prevented, batchItem message shouldn't be retried
        $this->preventProcessIfNeeded($batch, $batchItem);

        $batchItem->setAttempts($batchItem->getAttempts() + 1);
        $batchItem->setStartedAt(Carbon::now('UTC'));

        $messageFuncSuccess = true;

        try {
            return $func();
        } catch (Throwable $throwable) {
            $messageFuncSuccess = false;

            $batchItem->setStatus(
                $batchItem->canBeRetried()
                    ? BatchItemInterface::STATUS_FAILED_PENDING_RETRY
                    : BatchObjectInterface::STATUS_FAILED
            );
            $batchItem->setThrowable($throwable);

            throw $throwable;
        } finally {
            if ($messageFuncSuccess) {
                $batchItem->setStatus(
                    $batchItem->isApprovalRequired()
                        ? BatchObjectInterface::STATUS_SUCCEEDED_PENDING_APPROVAL
                        : BatchObjectInterface::STATUS_SUCCEEDED
                );
            }

            $batchItem->setFinishedAt(Carbon::now('UTC'));

            try {
                $batchItem = $this->batchItemRepository->save($batchItem);
            } catch (Throwable $throwableSaveBatchItem) {
                // If batchItem not saved and message logic failed, simply let it fail so the queue can retry it
                if ($messageFuncSuccess === false) {
                    throw $throwableSaveBatchItem;
                }

                // Otherwise, throw special exception to update batchItem in separate process
                throw new BatchItemProcessedButNotSavedException($batchItem, $throwableSaveBatchItem);
            }

            try {
                $this->batchProcessor
                    ->reset()
                    ->processBatchForBatchItem($this->batchObjectManager, $batch, $batchItem);
            } catch (Throwable $throwableProcessBatch) {
                // If batchItem can be retried and message logic failed, simply let it fail so the queue can retry it
                if ($batchItem->canBeRetried() && $messageFuncSuccess === false) {
                    throw $throwableProcessBatch;
                }

                // Otherwise, throw special exception to process batch for batchItem in separate process
                throw new BatchItemSavedButBatchNotProcessedException($batchItem, $throwableProcessBatch);
            }
        }
    }

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchCancelledException
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemCannotBeRetriedException
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemCompletedException
     */
    private function preventProcessIfNeeded(BatchInterface $batch, BatchItemInterface $batchItem): void
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

            $this->batchObjectManager->cancel($batchItem);

            throw $throwable;
        }
    }
}
