<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger;

use EonX\EasyAsync\Exceptions\Batch\BatchCancelledException;
use EonX\EasyAsync\Exceptions\Batch\BatchNotFoundException;
use EonX\EasyAsync\Interfaces\Batch\BatchItemFactoryInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchItemProcessorInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

final class ProcessBatchItemMiddleware implements MiddlewareInterface
{
    /**
     * @var \EonX\EasyAsync\Interfaces\Batch\BatchItemFactoryInterface
     */
    private $batchItemFactory;

    /**
     * @var \EonX\EasyAsync\Interfaces\Batch\BatchItemProcessorInterface
     */
    private $processor;

    public function __construct(BatchItemFactoryInterface $batchItemFactory, BatchItemProcessorInterface $processor)
    {
        $this->batchItemFactory = $batchItemFactory;
        $this->processor = $processor;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $batchId = $this->getBatchId($envelope);
        $func = $this->getNextClosure($envelope, $stack);

        // Skip if not from queue or no batchId on envelope
        if ($this->fromQueue($envelope) === false || $batchId === null) {
            return $func();
        }

        // Check for existing batchItem data on envelope
        $batchItemStamp = $this->getBatchItemStamp($envelope);
        $batchItemId = $batchItemStamp !== null ? $batchItemStamp->getBatchItemId() : null;
        $batchItemAttempts = $batchItemStamp !== null ? $batchItemStamp->getAttempts() : 0;

        $batchItem = $this->batchItemFactory->create($batchId, \get_class($envelope->getMessage()), $batchItemId);
        $batchItem->setAttempts($batchItemAttempts);

        try {
            return $this->processor->process($batchItem, $func);
        } catch (BatchNotFoundException | BatchCancelledException $exception) {
            // Do not retry if batch either not found or cancelled
            throw new UnrecoverableMessageHandlingException($exception->getMessage());
        } catch (\Throwable $throwable) {
            // Allow to handle retry for existing batchItem by setting id, attempts on envelope for retry
            $newBatchItemStamp = new BatchItemStamp((string)$batchItem->getId(), $batchItem->getAttempts());
            $withBatchItemId = $envelope->with($newBatchItemStamp);

            throw new HandlerFailedException($withBatchItemId, [$throwable]);
        }
    }

    private function fromQueue(Envelope $envelope): bool
    {
        return $envelope->last(ConsumedByWorkerStamp::class) !== null;
    }

    private function getBatchId(Envelope $envelope): ?string
    {
        /** @var null|\EonX\EasyAsync\Bridge\Symfony\Messenger\BatchStamp $stamp */
        $stamp = $envelope->last(BatchStamp::class);

        return $stamp !== null ? $stamp->getBatchId() : null;
    }

    private function getBatchItemStamp(Envelope $envelope): ?BatchItemStamp
    {
        /** @var null|\EonX\EasyAsync\Bridge\Symfony\Messenger\BatchItemStamp $stamp */
        $stamp = $envelope->last(BatchItemStamp::class);

        return $stamp;
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
