<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Listeners;

use EonX\EasyBatch\Events\BatchItemCancelledEvent;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchManagerInterface;

final class BatchItemCancelledListener
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchManagerInterface
     */
    private $batchManager;

    public function __construct(BatchManagerInterface $batchManager)
    {
        $this->batchManager = $batchManager;
    }

    public function __invoke(BatchItemCancelledEvent $event): void
    {
        $batchItem = $event->getBatchItem();

        // Cannot have any dependents if no name
        if ($batchItem->getName() === null) {
            return;
        }

        /** @var int|string $batchId */
        $batchId = $batchItem->getBatchId();
        $dependsOnName = $batchItem->getName();
        $func = function (BatchItemInterface $batchItem): void {
            $this->batchManager->cancel($batchItem);
        };

        $this->batchManager->iterateThroughItems($batchId, $func, $dependsOnName);
    }
}
