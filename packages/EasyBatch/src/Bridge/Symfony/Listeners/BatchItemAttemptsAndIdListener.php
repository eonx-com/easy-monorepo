<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Listeners;

use EonX\EasyBatch\Bridge\Symfony\Events\BatchItemCreatedForEnvelopeEvent;
use EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps\BatchItemStamp;

final class BatchItemAttemptsAndIdListener
{
    public function __invoke(BatchItemCreatedForEnvelopeEvent $event): void
    {
        $batchItem = $event->getBatchItem();
        $batchItemStamp = $event->getEnvelope()->last(BatchItemStamp::class);

        $batchItemId = $batchItemStamp !== null ? $batchItemStamp->getBatchItemId() : null;
        $batchItemAttempts = $batchItemStamp !== null ? $batchItemStamp->getAttempts() : 0;

        $batchItem
            ->setAttempts($batchItemAttempts)
            ->setId($batchItemId);
    }
}
