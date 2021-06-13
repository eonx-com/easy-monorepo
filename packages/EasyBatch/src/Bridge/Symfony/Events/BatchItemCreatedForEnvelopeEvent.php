<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Events;

use EonX\EasyBatch\Events\AbstractBatchItemEvent;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use Symfony\Component\Messenger\Envelope;

final class BatchItemCreatedForEnvelopeEvent extends AbstractBatchItemEvent
{
    /**
     * @var \Symfony\Component\Messenger\Envelope
     */
    private $envelope;

    public function __construct(BatchItemInterface $batchItem, Envelope $envelope)
    {
        $this->envelope = $envelope;

        parent::__construct($batchItem);
    }

    public function getEnvelope(): Envelope
    {
        return $this->envelope;
    }
}
