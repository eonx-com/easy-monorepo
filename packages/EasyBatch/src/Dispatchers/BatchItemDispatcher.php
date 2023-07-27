<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Dispatchers;

use EonX\EasyBatch\Interfaces\AsyncDispatcherInterface;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectManagerInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Iterator\BatchItemIterator;
use EonX\EasyBatch\Iterator\IteratorConfig;

final class BatchItemDispatcher
{
    public function __construct(
        private readonly AsyncDispatcherInterface $asyncDispatcher,
        private readonly BatchItemIterator $batchItemIterator,
        private readonly BatchItemRepositoryInterface $batchItemRepository,
        private readonly BatchRepositoryInterface $batchRepository,
    ) {
    }

    public function dispatchDependItems(
        BatchObjectManagerInterface $batchObjectManager,
        BatchItemInterface $batchItem,
    ): void {
        $this->doDispatch($batchObjectManager, $batchItem->getBatchId(), $batchItem->getName());
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    public function dispatchItemsForBatch(BatchObjectManagerInterface $batchObjectManager, BatchInterface $batch): void
    {
        $this->doDispatch($batchObjectManager, $batch->getIdOrFail());
    }

    private function doDispatch(
        BatchObjectManagerInterface $batchObjectManager,
        int|string $batchId,
        ?string $dependsOnName = null,
    ): void {
        // Update batchItems to status pending after current page is dispatched
        $currentPageCallback = function (array $items): void {
            $this->batchItemRepository->updateStatusToPending($items);
        };

        $func = function (BatchItemInterface $batchItem) use ($batchObjectManager): void {
            if ($batchItem->getType() === BatchItemInterface::TYPE_MESSAGE) {
                $this->asyncDispatcher->dispatchItem($batchItem);

                return;
            }

            if ($batchItem->getType() === BatchItemInterface::TYPE_NESTED_BATCH) {
                $batchObjectManager->dispatchBatch($this->batchRepository->findNestedOrFail($batchItem->getIdOrFail()));
            }
        };

        $iteratorConfig = IteratorConfig::create($batchId, $func, $dependsOnName)
            ->forDispatch()
            ->setCurrentPageCallback($currentPageCallback);

        $this->batchItemIterator->iterateThroughItems($iteratorConfig);
    }
}
