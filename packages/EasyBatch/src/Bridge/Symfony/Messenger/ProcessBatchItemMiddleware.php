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

    public function __construct(
        BatchRepositoryInterface $batchRepository,
        BatchItemRepositoryInterface $batchItemRepository,
        BatchManagerInterface $batchManager
    ) {
        $this->batchRepository = $batchRepository;
        $this->batchItemRepository = $batchItemRepository;
        $this->batchManager = $batchManager;
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
            $batch = $this->findBatch($batchItem->getBatchId());
            $message = $envelope->getMessage();

            if ($message instanceof CurrentBatchAwareInterface) {
                $message->setCurrentBatch($batch);
            }

            if ($message instanceof CurrentBatchItemAwareInterface) {
                $message->setCurrentBatchItem($batchItem);
            }

            return $this->batchManager->processItem($batch, $batchItem, $func);
        } catch (\Throwable $throwable) {
            // Do not retry if exception from package
            if ($throwable instanceof EasyBatchExceptionInterface) {
                throw new UnrecoverableMessageHandlingException($throwable->getMessage());
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
