<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger;

use EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps\BatchItemStamp;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchManagerInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Interfaces\CurrentBatchAwareInterface;
use EonX\EasyBatch\Interfaces\CurrentBatchItemAwareInterface;
use EonX\EasyBatch\Interfaces\EasyBatchExceptionInterface;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyLock\LockData;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

final class ProcessBatchItemMiddleware implements MiddlewareInterface
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface
     */
    private $batchItemRepository;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchManagerInterface
     */
    private $batchManager;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchRepositoryInterface
     */
    private $batchRepository;

    /**
     * @var \EonX\EasyLock\Interfaces\LockServiceInterface
     */
    private $lockService;

    public function __construct(
        BatchRepositoryInterface $batchRepository,
        BatchItemRepositoryInterface $batchItemRepository,
        BatchManagerInterface $batchManager,
        LockServiceInterface $lockService
    ) {
        $this->batchRepository = $batchRepository;
        $this->batchItemRepository = $batchItemRepository;
        $this->batchManager = $batchManager;
        $this->lockService = $lockService;
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemNotFoundException
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $func = $this->getNextClosure($envelope, $stack);
        $batchItemStamp = $envelope->last(BatchItemStamp::class);
        $consumedByWorkerStamp = $envelope->last(ConsumedByWorkerStamp::class);

        // Proceed only if consumed by worker or current envelope is for a batchItem
        if ($consumedByWorkerStamp === null || $batchItemStamp === null) {
            return $func();
        }

        try {
            $batchItem = $this->findBatchItem($batchItemStamp->getBatchItemId());
            $message = $envelope->getMessage();

            // Since items can be dispatched multiple times to guarantee all items are dispatched
            // We must protect the processing logic with a lock to make sure the same item isn't processed
            // by multiple workers concurrently.
            $lockData = LockData::create(\sprintf('easy_batch_item_%s', (string)$batchItem->getId()));

            $result = $this->lockService->processWithLock($lockData, function () use ($batchItem, $message, $func) {
                $batch = $this->findBatch($batchItem->getBatchId());

                if ($message instanceof CurrentBatchAwareInterface) {
                    $message->setCurrentBatch($batch);
                }

                if ($message instanceof CurrentBatchItemAwareInterface) {
                    $message->setCurrentBatchItem($batchItem);
                }

                return $this->batchManager->processItem($batch, $batchItem, $func);
            });

            // If lock not acquired, return envelope
            return $result === null ? $envelope : $result;
        } catch (\Throwable $throwable) {
            // Do not retry if exception from package
            if ($throwable instanceof EasyBatchExceptionInterface) {
                throw new UnrecoverableMessageHandlingException(
                    $throwable->getMessage(),
                    $throwable->getCode(),
                    $throwable
                );
            }

            if (($throwable instanceof HandlerFailedException)
                || ($throwable instanceof UnrecoverableMessageHandlingException)) {
                throw $throwable;
            }

            throw new HandlerFailedException($envelope, [$throwable]);
        }
    }

    /**
     * @param int|string $batchId
     *
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     */
    private function findBatch($batchId): BatchInterface
    {
        return $this->batchRepository->findOrFail($batchId);
    }

    /**
     * @param int|string $batchItemId
     *
     * @throws \EonX\EasyBatch\Exceptions\BatchItemNotFoundException
     */
    private function findBatchItem($batchItemId): BatchItemInterface
    {
        return $this->batchItemRepository->findOrFail($batchItemId);
    }

    private function getNextClosure(Envelope $envelope, StackInterface $stack): \Closure
    {
        return static function () use ($envelope, $stack): Envelope {
            return $stack
                ->next()
                ->handle($envelope, $stack);
        };
    }
}
