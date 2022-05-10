<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger;

use EonX\EasyBatch\Bridge\Symfony\Messenger\Emergency\ProcessBatchForBatchItemMessage;
use EonX\EasyBatch\Bridge\Symfony\Messenger\Emergency\UpdateBatchItemMessage;
use EonX\EasyBatch\Exceptions\BatchItemProcessedButNotSavedException;
use EonX\EasyBatch\Exceptions\BatchItemSavedButBatchNotProcessedException;
use EonX\EasyBatch\Interfaces\EasyBatchEmergencyExceptionInterface;
use EonX\EasyBatch\Interfaces\EasyBatchExceptionInterface;
use EonX\EasyBatch\Transformers\BatchItemTransformer;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface as MessengerExceptionInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Transport\TransportInterface;

final class BatchItemExceptionHandler
{
    public function __construct(
        private readonly BatchItemTransformer $batchItemTransformer,
        private readonly ContainerInterface $container,
        private readonly string $emergencyTransportName = 'async'
    ) {
    }

    /**
     * @throws \Symfony\Component\Messenger\Exception\ExceptionInterface
     * @throws \Throwable
     */
    public function handleException(\Throwable $throwable, Envelope $envelope): Envelope
    {
        // Emergency exceptions has special behaviour
        if ($throwable instanceof EasyBatchEmergencyExceptionInterface) {
            return $this->doHandleEmergencyException($throwable, $envelope);
        }

        // Do not retry if exception from package
        if ($throwable instanceof EasyBatchExceptionInterface) {
            throw new UnrecoverableMessageHandlingException(
                $throwable->getMessage(),
                $throwable->getCode(),
                $throwable
            );
        }

        // Simply bubble up messenger exceptions
        if ($throwable instanceof MessengerExceptionInterface) {
            throw $throwable;
        }

        // Otherwise, wrap all other exceptions in handler failed exception
        throw new HandlerFailedException($envelope, [$throwable]);
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function doHandleEmergencyException(
        EasyBatchEmergencyExceptionInterface $exception,
        Envelope $envelope
    ): Envelope {
        $message = null;

        if ($exception instanceof BatchItemSavedButBatchNotProcessedException) {
            $batchItem = $exception->getBatchItem();
            $message = new ProcessBatchForBatchItemMessage($batchItem->getId());
        }

        if ($exception instanceof BatchItemProcessedButNotSavedException) {
            $batchItem = $exception->getBatchItem();
            $message = new UpdateBatchItemMessage(
                $batchItem->getId(),
                $this->batchItemTransformer->transformToArray($batchItem)
            );
        }

        if ($message !== null) {
            $this->getEmergencyTransport($envelope)
                ->send(Envelope::wrap($message));
        }

        return $envelope;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function getEmergencyTransport(Envelope $envelope): TransportInterface
    {
        $stamp = $envelope->last(ReceivedStamp::class);
        $transportName = $stamp instanceof ReceivedStamp ? $stamp->getTransportName() : $this->emergencyTransportName;

        return $this->container->get($transportName);
    }
}
