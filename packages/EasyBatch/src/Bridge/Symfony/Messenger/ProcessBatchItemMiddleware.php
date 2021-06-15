<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger;

use EonX\EasyBatch\Bridge\Symfony\Events\BatchItemCreatedForEnvelopeEvent;
use EonX\EasyBatch\Bridge\Symfony\Events\BatchItemFailedForEnvelopeEvent;
use EonX\EasyBatch\Bridge\Symfony\Events\BatchItemHandledForEnvelopeEvent;
use EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps\BatchItemClassStamp;
use EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps\BatchStamp;
use EonX\EasyBatch\Exceptions\BatchCancelledException;
use EonX\EasyBatch\Exceptions\BatchNotFoundException;
use EonX\EasyBatch\Interfaces\BatchItemFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemProcessorInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class ProcessBatchItemMiddleware implements MiddlewareInterface
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchItemFactoryInterface
     */
    private $batchItemFactory;

    /**
     * @var \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchItemProcessorInterface
     */
    private $processor;

    public function __construct(
        BatchItemFactoryInterface $batchItemFactory,
        BatchItemProcessorInterface $processor,
        EventDispatcherInterface $dispatcher
    ) {
        $this->batchItemFactory = $batchItemFactory;
        $this->processor = $processor;
        $this->dispatcher = $dispatcher;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $batchId = $this->getBatchId($envelope);
        $func = $this->getNextClosure($envelope, $stack);

        // Skip if not from queue or no batchId on envelope
        if ($this->fromQueue($envelope) === false || $batchId === null) {
            return $func();
        }

        $batchItem = $this->batchItemFactory->create($batchId, \get_class($envelope->getMessage()), $this->getBatchItemClass($envelope));
        $event = new BatchItemCreatedForEnvelopeEvent($batchItem, $envelope);

        $this->dispatcher->dispatch($event);

        $batchItem = $event->getBatchItem();

        try {
            /** @var \Symfony\Component\Messenger\Envelope $newEnvelope */
            $newEnvelope = $this->processor->process($batchItem, $func);

            $handledEvent = new BatchItemHandledForEnvelopeEvent($batchItem, $newEnvelope);

            $this->dispatcher->dispatch($handledEvent);

            return $handledEvent->getEnvelope();
        } catch (BatchNotFoundException | BatchCancelledException $exception) {
            // Do not retry if batch either not found or cancelled
            throw new UnrecoverableMessageHandlingException($exception->getMessage());
        } catch (\Throwable $throwable) {
            $failedEvent = new BatchItemFailedForEnvelopeEvent($batchItem, $envelope, $throwable);

            $this->dispatcher->dispatch($event);

            throw new HandlerFailedException($failedEvent->getEnvelope(), [$failedEvent->getThrowable()]);
        }
    }

    private function fromQueue(Envelope $envelope): bool
    {
        return $envelope->last(ConsumedByWorkerStamp::class) !== null;
    }

    /**
     * @return null|int|string
     */
    private function getBatchId(Envelope $envelope)
    {
        $stamp = $envelope->last(BatchStamp::class);

        return $stamp !== null ? $stamp->getBatchId() : null;
    }

    private function getBatchItemClass(Envelope $envelope): ?string
    {
        $stamp = $envelope->last(BatchItemClassStamp::class);

        return $stamp !== null ? $stamp->getClass() : null;
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
