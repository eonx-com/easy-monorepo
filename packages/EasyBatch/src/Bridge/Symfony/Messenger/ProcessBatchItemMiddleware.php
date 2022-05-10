<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger;

use EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps\BatchItemStamp;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Interfaces\CurrentBatchAwareInterface;
use EonX\EasyBatch\Interfaces\CurrentBatchItemAwareInterface;
use EonX\EasyBatch\Processors\BatchItemProcessor;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyLock\LockData;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

final class ProcessBatchItemMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly BatchItemExceptionHandler $batchItemExceptionHandler,
        private readonly BatchItemRepositoryInterface $batchItemRepository,
        private readonly BatchItemProcessor $batchItemProcessor,
        private readonly LockServiceInterface $lockService
    ) {
    }

    /**
     * @throws \Symfony\Component\Messenger\Exception\ExceptionInterface
     * @throws \Throwable
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $func = $this->getNextClosure($envelope, $stack);
        $batchItemStamp = $envelope->last(BatchItemStamp::class);
        $consumedByWorkerStamp = $envelope->last(ConsumedByWorkerStamp::class);
        $message = $envelope->getMessage();

        // Proceed only if consumed by worker or current envelope is for a batchItem
        if ($consumedByWorkerStamp === null || $batchItemStamp === null) {
            return $func();
        }

        try {
            // Since items can be dispatched multiple times to guarantee all items are dispatched
            // We must protect the processing logic with a lock to make sure the same item isn't processed
            // by multiple workers concurrently.
            $result = $this->processBatchItemWithLock(
                $batchItemStamp->getBatchItemId(),
                function () use ($batchItemStamp, $message, $func) {
                    $batchItem = $this->batchItemRepository->findForProcess($batchItemStamp->getBatchItemId());
                    $batch = $this->batchRepository
                        ->reset()
                        ->findOrFail($batchItem->getBatchId());

                    if ($message instanceof CurrentBatchAwareInterface) {
                        $message->setCurrentBatch($batch);
                    }

                    if ($message instanceof CurrentBatchItemAwareInterface) {
                        $message->setCurrentBatchItem($batchItem);
                    }

                    return $this->batchItemProcessor->processBatchItem($batch, $batchItem, $func);
                }
            );

            // If lock not acquired, return envelope
            return $result === null ? $envelope : $result;
        } catch (\Throwable $throwable) {
            return $this->batchItemExceptionHandler->handleException($throwable, $envelope);
        }
    }

    private function getNextClosure(Envelope $envelope, StackInterface $stack): \Closure
    {
        return static function () use ($envelope, $stack): Envelope {
            return $stack
                ->next()
                ->handle($envelope, $stack);
        };
    }

    private function processBatchItemWithLock(int|string $batchItemId, callable $func): mixed
    {
        $lockData = LockData::create(\sprintf('easy_batch_item_%s', $batchItemId), null, true);

        return $this->lockService->processWithLock($lockData, $func);
    }
}
