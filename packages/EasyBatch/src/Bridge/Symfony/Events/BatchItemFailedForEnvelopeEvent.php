<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Events;

use EonX\EasyBatch\Interfaces\BatchItemInterface;
use Symfony\Component\Messenger\Envelope;

final class BatchItemFailedForEnvelopeEvent extends AbstractBatchItemForEnvelopeEvent
{
    /**
     * @var \Throwable
     */
    private $throwable;

    public function __construct(BatchItemInterface $batchItem, Envelope $envelope, \Throwable $throwable)
    {
        $this->throwable = $throwable;

        parent::__construct($batchItem, $envelope);
    }

    public function getThrowable(): \Throwable
    {
        return $this->throwable;
    }
}
