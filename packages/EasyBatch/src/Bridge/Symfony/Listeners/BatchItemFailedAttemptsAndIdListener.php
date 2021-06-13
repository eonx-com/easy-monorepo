<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Listeners;

use EonX\EasyBatch\Bridge\Symfony\Events\BatchItemFailedForEnvelopeEvent;
use EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps\BatchItemStamp;

final class BatchItemFailedAttemptsAndIdListener
{
    public function __invoke(BatchItemFailedForEnvelopeEvent $event): void
    {
        $batchItem = $event->getBatchItem();

        // Allow to handle retry for existing batchItem by setting id, attempts on envelope for retry
        $newBatchItemStamp = new BatchItemStamp($batchItem->getId(), $batchItem->getAttempts());

        $event->setEnvelope($event->getEnvelope()->with($newBatchItemStamp));
    }
}
