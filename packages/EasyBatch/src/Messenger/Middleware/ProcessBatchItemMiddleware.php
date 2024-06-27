<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Messenger\Middleware;

use Closure;
use EonX\EasyBatch\Common\Exception\BatchItemNotHandledException;
use EonX\EasyBatch\Common\Processor\BatchItemProcessor;
use EonX\EasyBatch\Common\Processor\BatchProcessor;
use EonX\EasyBatch\Common\Repository\BatchItemRepositoryInterface;
use EonX\EasyBatch\Common\Repository\BatchRepositoryInterface;
use EonX\EasyBatch\Common\ValueObject\CurrentBatchObjectsAwareInterface;
use EonX\EasyBatch\Messenger\ExceptionHandler\BatchItemExceptionHandler;
use EonX\EasyBatch\Messenger\Factory\BatchItemLockFactoryInterface;
use EonX\EasyBatch\Messenger\Stamp\BatchItemStamp;
use EonX\EasyLock\Common\Locker\LockerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Throwable;

final readonly class ProcessBatchItemMiddleware implements MiddlewareInterface
{
    public function __construct(
        private BatchRepositoryInterface $batchRepository,
        private BatchItemExceptionHandler $batchItemExceptionHandler,
        private BatchItemRepositoryInterface $batchItemRepository,
        private BatchItemProcessor $batchItemProcessor,
        private BatchItemLockFactoryInterface $batchItemLockFactory,
        private BatchProcessor $batchProcessor,
        private LockerInterface $locker,
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
            // by multiple workers concurrently
            $result = $this->locker->processWithLock(
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
    ): Closure {
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
