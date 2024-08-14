<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Dispatcher;

use EonX\EasyBatch\Common\Enum\BatchItemType;
use EonX\EasyBatch\Common\Iterator\BatchItemIteratorInterface;
use EonX\EasyBatch\Common\Manager\BatchObjectManagerInterface;
use EonX\EasyBatch\Common\Repository\BatchItemRepositoryInterface;
use EonX\EasyBatch\Common\Repository\BatchRepositoryInterface;
use EonX\EasyBatch\Common\ValueObject\Batch;
use EonX\EasyBatch\Common\ValueObject\BatchItem;
use EonX\EasyBatch\Common\ValueObject\BatchItemIteratorConfig;

final readonly class BatchItemDispatcher
{
    public function __construct(
        private AsyncDispatcherInterface $asyncDispatcher,
        private BatchItemIteratorInterface $batchItemIterator,
        private BatchItemRepositoryInterface $batchItemRepository,
        private BatchRepositoryInterface $batchRepository,
    ) {
    }

    public function dispatchDependItems(
        BatchObjectManagerInterface $batchObjectManager,
        BatchItem $batchItem,
    ): void {
        $this->doDispatch($batchObjectManager, $batchItem->getBatchId(), $batchItem->getName());
    }

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
     */
    public function dispatchItemsForBatch(BatchObjectManagerInterface $batchObjectManager, Batch $batch): void
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

        $func = function (BatchItem $batchItem) use ($batchObjectManager): void {
            if ($batchItem->getType() === BatchItemType::Message->value) {
                $this->asyncDispatcher->dispatchItem($batchItem);

                return;
            }

            if ($batchItem->getType() === BatchItemType::NestedBatch->value) {
                $batchObjectManager->dispatchBatch($this->batchRepository->findNestedOrFail($batchItem->getIdOrFail()));
            }
        };

        $iteratorConfig = BatchItemIteratorConfig::create($batchId, $func, $dependsOnName)
            ->forDispatch()
            ->setCurrentPageCallback($currentPageCallback);

        $this->batchItemIterator->iterateThroughItems($iteratorConfig);
    }
}
