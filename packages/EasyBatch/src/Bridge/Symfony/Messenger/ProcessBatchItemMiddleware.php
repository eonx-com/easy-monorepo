<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger;

use EonX\EasyBatch\Bridge\Symfony\Messenger\Lock\BatchItemLockFactoryInterface;
use EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps\BatchItemStamp;
use EonX\EasyBatch\Exceptions\BatchItemNotHandledException;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Interfaces\CurrentBatchObjectsAwareInterface;
use EonX\EasyBatch\Processors\BatchItemProcessor;
use EonX\EasyBatch\Processors\BatchProcessor;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Throwable;

final class ProcessBatchItemMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly BatchItemExceptionHandler $batchItemExceptionHandler,
        private readonly BatchItemRepositoryInterface $batchItemRepository,
        private readonly BatchItemProcessor $batchItemProcessor,
        private readonly BatchItemLockFactoryInterface $batchItemLockFactory,
        private readonly BatchProcessor $batchProcessor,
        private readonly LockServiceInterface $lockService,
    ) {
    }

    /**
     * @throws \Symfony\Component\Messenger\Exception\ExceptionInterface
     * @throws \Throwable
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $batchItemStamp = $envelope->last(BatchItemStamp::class);
        $consumedByWorkerStamp = $envelope->last(ConsumedByWorkerStamp::class);
        $func = $this->getNextClosure($envelope, $stack, $batchItemStamp, $consumedByWorkerStamp);
        $message = $envelope->getMessage();

        // Make sure to ALWAYS have a clean batch processor to prevent any caching issue in worker
        $this->batchProcessor->reset();

        // Proceed only if consumed by worker or current envelope is for a batchItem
        if ($consumedByWorkerStamp === null || $batchItemStamp === null) {
            return $func();
        }

        try {
            // Since items can be dispatched multiple times to guarantee all items are dispatched
            // We must protect the processing logic with a lock to make sure the same item isn't processed
            // by multiple workers concurrently.
            $result = $this->lockService->processWithLock(
                $this->batchItemLockFactory->createFromEnvelope($envelope),
                function () use ($batchItemStamp, $message, $func) {
                    $batchItem = $this->batchItemRepository->findForProcess($batchItemStamp->getBatchItemId());
                    $batch = $this->batchRepository
                        ->reset()
                        ->findOrFail($batchItem->getBatchId());

                    if ($message instanceof CurrentBatchObjectsAwareInterface) {
                        $message->setCurrentBatchObjects($batch, $batchItem);
                    }

                    return $this->batchItemProcessor->processBatchItem($batch, $batchItem, $func);
                }
            );

            // If lock not acquired, return envelope
            return $result ?? $envelope;
        } catch (Throwable $throwable) {
            return $this->batchItemExceptionHandler->handleException($throwable, $envelope);
        } finally {
            if ($message instanceof CurrentBatchObjectsAwareInterface) {
                $message->unsetCurrentBatchObjects();
            }
        }
    }

    private function getNextClosure(
        Envelope $envelope,
        StackInterface $stack,
        ?BatchItemStamp $batchItemStamp = null,
        ?ConsumedByWorkerStamp $consumedByWorkerStamp = null,
    ): \Closure {
        return static function () use ($envelope, $stack, $batchItemStamp, $consumedByWorkerStamp): Envelope {
            $newEnvelope = $stack
                ->next()
                ->handle($envelope, $stack);

            if ($batchItemStamp !== null
                && $consumedByWorkerStamp !== null
                && $newEnvelope->last(HandledStamp::class) === null) {
                throw new BatchItemNotHandledException(\sprintf(
                    'Batch item "%s" was not handled by any handler.',
                    $batchItemStamp->getBatchItemId()
                ));
            }

            return $newEnvelope;
        };
    }
}
