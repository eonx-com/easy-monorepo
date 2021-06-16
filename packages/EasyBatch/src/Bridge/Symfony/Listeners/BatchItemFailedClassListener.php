<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Listeners;

use EonX\EasyBatch\Bridge\Symfony\Events\BatchItemFailedForEnvelopeEvent;
use EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps\BatchItemClassStamp;

final class BatchItemFailedClassListener
{
    public function __invoke(BatchItemFailedForEnvelopeEvent $event): void
    {
        $stamp = new BatchItemClassStamp(\get_class($event->getBatchItem()));

        $event->setEnvelope($event->getEnvelope()->with($stamp));
    }
}
