<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Listeners;

use EonX\EasyBatch\Events\BatchCancelledEvent;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchManagerInterface;

final class BatchCancelledListener
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchManagerInterface
     */
    private $batchManager;

    public function __construct(BatchManagerInterface $batchManager)
    {
        $this->batchManager = $batchManager;
    }

    public function __invoke(BatchCancelledEvent $event): void
    {
        /** @var int|string $batchId */
        $batchId = $event->getBatch()
            ->getId();

        $this->batchManager->iterateThroughItems($batchId, function (BatchItemInterface $batchItem): void {
            $this->batchManager->cancel($batchItem);
        });
    }
}
