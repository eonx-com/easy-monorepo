<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Listeners;

use EonX\EasyBatch\Bridge\Symfony\Events\BatchItemCreatedForEnvelopeEvent;
use EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps\BatchObjectRequiresApprovalStamp;

final class BatchItemRequiresApprovalListener
{
    public function __invoke(BatchItemCreatedForEnvelopeEvent $event): void
    {
        $requiresApprovalStamp = $event->getEnvelope()->last(BatchObjectRequiresApprovalStamp::class);

        $event
            ->getBatchItem()
            ->setApprovalRequired($requiresApprovalStamp instanceof BatchObjectRequiresApprovalStamp);
    }
}
