<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Listeners;

use EonX\EasyBatch\Bridge\Symfony\Events\BatchItemFailedForEnvelopeEvent;
use EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps\BatchObjectRequiresApprovalStamp;

final class BatchItemFailedRequiresApprovalListener
{
    public function __invoke(BatchItemFailedForEnvelopeEvent $event): void
    {
        // Make sure to carry approval through retries
        if ($event->getBatchItem()->isApprovalRequired()) {
            $event->setEnvelope($event->getEnvelope()->with(new BatchObjectRequiresApprovalStamp()));
        }
    }
}
