<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Listeners;

use EonX\EasyBatch\Events\BatchItemCompletedEvent;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchManagerInterface;

final class BatchItemCompletedListener
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchManagerInterface
     */
    private $batchManager;

    public function __construct(BatchManagerInterface $batchManager)
    {
        $this->batchManager = $batchManager;
    }

    public function __invoke(BatchItemCompletedEvent $event): void
    {
        $batchItem = $event->getBatchItem();

        // Cannot have any dependents if no name
        if ($batchItem->getName() === null) {
            return;
        }

        /** @var int|string $batchId */
        $batchId = $batchItem->getBatchId();
        $dependsOnName = $batchItem->getName();
        $status = $batchItem->getStatus();

        $func = function (BatchItemInterface $batchItem) use ($status): void {
            $status === BatchItemInterface::STATUS_SUCCEEDED
                ? $this->batchManager->dispatchItem($batchItem)
                : $this->batchManager->cancel($batchItem);
        };

        $this->batchManager->iterateThroughItems($batchId, $func, $dependsOnName);
    }
}
