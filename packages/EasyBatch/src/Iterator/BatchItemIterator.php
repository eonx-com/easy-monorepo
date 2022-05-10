<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Iterator;

use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyPagination\Pagination;

final class BatchItemIterator
{
    private const PAGINATE_METHOD = 'paginateItems';

    private const PAGINATE_FOR_DISPATCH_METHOD = 'paginateItemsForDispatch';

    public function __construct(
        private readonly BatchItemRepositoryInterface $batchItemRepository,
        private readonly int $batchItemsPerPage
    ) {
    }

    public function iterateThroughItems(IteratorConfig $config): void
    {
        $page = 1;
        $pagesCache = [];

        do {
            // This method isn't used only to dispatch batch items
            // But also to perform actions on batch or batch items completion
            // Since findForDispatch was updated to filter on pending status
            // This breaks some part of the package, it needs to be refactored
            // To support multiple paginator queries

            $paginateMethod = $config->isForDispatch() ? self::PAGINATE_FOR_DISPATCH_METHOD : self::PAGINATE_METHOD;
            $paginator = $this->batchItemRepository->{$paginateMethod}(
                new Pagination($page, $config->getBatchItemsPerPage() ?? $this->batchItemsPerPage),
                $config->getBatchId(),
                $config->getDependsOnName()
            );

            /** @var \EonX\EasyBatch\Interfaces\BatchItemInterface[] $items */
            $items = $paginator->getItems();

            // Check hasNextPage before iterating through items in case the logic modifies the pagination,
            // It would impact the total number of pages as well.
            $hasNextPage = $paginator->hasNextPage();

            // Implement hash and cache mechanism to prevent infinite loop due to resetPagination logic.
            // resetPagination should apply only when pagination actually changed
            $pageHash = $this->generateItemPageHash($page, $items);
            $pageAlreadyProcessed = isset($pagesCache[$pageHash]);
            $pagesCache[$pageHash] = true;

            if ($pageAlreadyProcessed === false) {
                foreach ($items as $item) {
                    \call_user_func($config->getFunc(), $item, $this->batchItemRepository);
                }
            }

            // Allow logic to be run on current page items
            if ($config->getCurrentPageCallback() !== null) {
                \call_user_func($config->getCurrentPageCallback(), $items);
            }

            // Since pagination is based on status, it gets modified as items are processed
            // which results in missing items to dispatch. The solution is to reset the pagination until all items
            // have been dispatched as expected.
            // Reset pagination only when not on first page, and last page was reached.
            // Because no more items to dispatch means 0 items in first page.
            $resetPagination = $page > 1 && $hasNextPage === false;
            $page = $resetPagination ? 1 : $page + 1;
        } while (($hasNextPage || $resetPagination) && $pageAlreadyProcessed === false);
    }

    /**
     * @param \EonX\EasyBatch\Interfaces\BatchItemInterface[] $batchItems
     */
    private function generateItemPageHash(int $page, array $batchItems): string
    {
        $hash = (string)$page;

        foreach ($batchItems as $batchItem) {
            $hash .= $batchItem->getId();
        }

        return \md5($hash);
    }
}
