<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Doctrine\Iterator;

use Doctrine\DBAL\Query\QueryBuilder;
use EonX\EasyBatch\Common\Enum\BatchObjectStatus;
use EonX\EasyBatch\Common\Iterator\BatchItemIteratorInterface;
use EonX\EasyBatch\Common\Repository\BatchItemRepositoryInterface;
use EonX\EasyBatch\Common\ValueObject\BatchItemIteratorConfig;
use EonX\EasyPagination\Paginator\ExtendablePaginatorInterface;
use EonX\EasyPagination\Paginator\LengthAwarePaginatorInterface;
use EonX\EasyPagination\ValueObject\Pagination;

final readonly class BatchItemIterator implements BatchItemIteratorInterface
{
    public function __construct(
        private BatchItemRepositoryInterface $batchItemRepository,
        private int $batchItemsPerPage,
    ) {
    }

    public function iterateThroughItems(BatchItemIteratorConfig $config): void
    {
        $page = 1;
        $pagesCache = [];

        $this->processConfig($config);

        do {
            $paginator = $this->batchItemRepository->paginateItems(
                new Pagination($page, $config->getBatchItemsPerPage() ?? $this->batchItemsPerPage),
                $config->getBatchId(),
                $config->getDependsOnName()
            );

            if ($config->getExtendPaginator() !== null) {
                $newPaginator = \call_user_func($config->getExtendPaginator(), $paginator);
                $paginator = $newPaginator instanceof LengthAwarePaginatorInterface ? $newPaginator : $paginator;
            }

            /** @var \EonX\EasyBatch\Common\ValueObject\BatchItemInterface[] $items */
            $items = $paginator->getItems();

            // Check hasNextPage before iterating through items in case the logic modifies the pagination,
            // It would impact the total number of pages as well
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
            // Because no more items to dispatch means 0 items in first page
            $resetPagination = $page > 1 && $hasNextPage === false;
            $page = $resetPagination ? 1 : $page + 1;
        } while (($hasNextPage || $resetPagination) && $pageAlreadyProcessed === false);
    }

    /**
     * @param \EonX\EasyBatch\Common\ValueObject\BatchItemInterface[] $batchItems
     */
    private function generateItemPageHash(int $page, array $batchItems): string
    {
        $hash = (string)$page;

        foreach ($batchItems as $batchItem) {
            $hash .= $batchItem->getId();
        }

        return \md5($hash);
    }

    private function processConfig(BatchItemIteratorConfig $config): void
    {
        $quote = static fn (
            QueryBuilder $queryBuilder,
            array $statuses,
        ): array => \array_map(
            static fn (BatchObjectStatus $status): string => $queryBuilder->getConnection()
                ->quote($status->value),
            $statuses
        );

        if ($config->isForCancel()) {
            $config->setExtendPaginator(static function (ExtendablePaginatorInterface $paginator) use ($quote): void {
                $paginator->addFilterCriteria(static function (QueryBuilder $queryBuilder) use ($quote): void {
                    $queryBuilder
                        ->andWhere($queryBuilder->expr()->notIn(
                            'status',
                            $quote($queryBuilder, BatchObjectStatus::STATUSES_FOR_COMPLETE)
                        ));
                });
            });
        }

        if ($config->isForDispatch()) {
            $config->setExtendPaginator(static function (ExtendablePaginatorInterface $paginator) use ($quote): void {
                $paginator->addFilterCriteria(static function (QueryBuilder $queryBuilder) use ($quote): void {
                    $queryBuilder
                        ->andWhere($queryBuilder->expr()->in(
                            'status',
                            $quote($queryBuilder, BatchObjectStatus::STATUSES_FOR_DISPATCH)
                        ));
                });
            });
        }
    }
}
