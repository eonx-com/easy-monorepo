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

        // Check if batch item requires approval
        $batchItemRequiresApprovalStamp = $this->getBatchItemRequiresApprovalStamp($envelope);

        $batchItem = $this->batchItemFactory->create($batchId, \get_class($envelope->getMessage()), $batchItemId);
        $batchItem->setAttempts($batchItemAttempts);
        $batchItem->setRequiresApproval($batchItemRequiresApprovalStamp instanceof BatchItemRequiresApprovalStamp);

        try {
            return $this->processor->process($batchItem, $func);
        } catch (BatchNotFoundException | BatchCancelledException $exception) {
            // Do not retry if batch either not found or cancelled
            throw new UnrecoverableMessageHandlingException($exception->getMessage());
        } catch (\Throwable $throwable) {
            // Allow to handle retry for existing batchItem by setting id, attempts on envelope for retry
            $newBatchItemStamp = new BatchItemStamp($batchItem->getId(), $batchItem->getAttempts());
            $newEnvelope = $envelope->with($newBatchItemStamp);

            // Make sure to carry approval through retries
            if ($batchItemRequiresApprovalStamp instanceof BatchItemRequiresApprovalStamp) {
                $newEnvelope = $newEnvelope->with($batchItemRequiresApprovalStamp);
            }

            throw new HandlerFailedException($newEnvelope, [$throwable]);
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

    private function getBatchItemRequiresApprovalStamp(Envelope $envelope): ?BatchItemRequiresApprovalStamp
    {
        return $envelope->last(BatchItemRequiresApprovalStamp::class);
    }

    private function getBatchItemStamp(Envelope $envelope): ?BatchItemStamp
    {
        return $envelope->last(BatchItemStamp::class);
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
