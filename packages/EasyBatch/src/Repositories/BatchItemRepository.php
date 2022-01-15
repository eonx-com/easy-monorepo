<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Repositories;

use Doctrine\DBAL\Query\QueryBuilder;
use EonX\EasyBatch\Exceptions\BatchItemNotFoundException;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Paginators\DoctrineDbalLengthAwarePaginator;

final class BatchItemRepository extends AbstractBatchObjectRepository implements BatchItemRepositoryInterface
{
    /**
     * @param int|string $batchId
     */
    public function findForDispatch(
        StartSizeDataInterface $startSizeData,
        $batchId,
        ?string $dependsOnName = null
    ): LengthAwarePaginatorInterface {
        $paginator = new DoctrineDbalLengthAwarePaginator($this->conn, $this->table, $startSizeData);

        $paginator->setCriteria(static function (QueryBuilder $queryBuilder) use ($batchId, $dependsOnName): void {
            $queryBuilder
                ->where('batch_id = :batchId')
                ->setParameter('batchId', $batchId);

            // Dispatch only pending items
            $queryBuilder
                ->andWhere('status = :pendingStatus')
                ->setParameter('pendingStatus', BatchObjectInterface::STATUS_PENDING);

            // Make sure to get only batchItems with no dependency
            if ($dependsOnName === null) {
                $queryBuilder->andWhere('depends_on_name is null');
            }

            // Make sure to get only batchItems for given dependency
            if ($dependsOnName !== null) {
                $queryBuilder
                    ->andWhere('depends_on_name = :dependsOnName')
                    ->setParameter('dependsOnName', $dependsOnName);
            }
        });

        $paginator->setGetItemsCriteria(static function (QueryBuilder $queryBuilder): void {
            $queryBuilder->orderBy('created_at');
        });

        $paginator->setTransformer(function (array $item): BatchItemInterface {
            /** @var \EonX\EasyBatch\Interfaces\BatchItemInterface $batchItem */
            $batchItem = $this->factory->createFromArray($item);

            return $batchItem;
        });

        return $paginator;
    }

    /**
     * @param int|string $batchItemId
     *
     * @throws \Doctrine\DBAL\Exception
     * @throws \EonX\EasyBatch\Exceptions\BatchItemNotFoundException
     */
    public function findOrFail($batchItemId): BatchItemInterface
    {
        /** @var null|\EonX\EasyBatch\Interfaces\BatchItemInterface $batchItem */
        $batchItem = $this->doFind($batchItemId);

        if ($batchItem !== null) {
            return $batchItem;
        }

        throw new BatchItemNotFoundException(\sprintf('BatchItem for id "%s" not found', $batchItemId));
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function save(BatchItemInterface $batchItem): BatchItemInterface
    {
        $this->doSave($batchItem);

        return $batchItem;
    }
}
