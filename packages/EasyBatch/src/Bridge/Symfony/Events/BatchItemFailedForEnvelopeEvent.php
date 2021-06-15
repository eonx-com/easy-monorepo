<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Events;

use EonX\EasyBatch\Events\AbstractBatchItemEvent;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use Symfony\Component\Messenger\Envelope;

final class BatchItemFailedForEnvelopeEvent extends AbstractBatchItemEvent
{
    /**
     * @var \Symfony\Component\Messenger\Envelope
     */
    private $envelope;

    /**
     * @var \Throwable
     */
    private $throwable;

    public function __construct(BatchItemInterface $batchItem, Envelope $envelope, \Throwable $throwable)
    {
        $this->envelope = $envelope;
        $this->throwable = $throwable;

        parent::__construct($batchItem);
    }

    public function getEnvelope(): Envelope
    {
        return $this->envelope;
    }

    public function getThrowable(): \Throwable
    {
        return $this->throwable;
    }

    public function setEnvelope(Envelope $envelope): void
    {
        $this->envelope = $envelope;
    }
}
