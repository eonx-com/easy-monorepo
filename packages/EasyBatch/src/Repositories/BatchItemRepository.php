<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Repositories;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;
use EonX\EasyBatch\Exceptions\BatchItemNotFoundException;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Paginators\DoctrineDbalLengthAwarePaginator;

final class BatchItemRepository extends AbstractBatchObjectRepository implements BatchItemRepositoryInterface
{
    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \EonX\EasyBatch\Exceptions\BatchItemNotFoundException
     */
    public function findForProcess(int|string $batchItemId): BatchItemInterface
    {
        $batchItem = $this->findOrFail($batchItemId);

        if ($batchItem->getStatus() === BatchObjectInterface::STATUS_CREATED) {
            $this->updateStatusToPending([$batchItem]);
        }

        return $batchItem;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \EonX\EasyBatch\Exceptions\BatchItemNotFoundException
     */
    public function findOrFail(int|string $batchItemId): BatchItemInterface
    {
        /** @var null|\EonX\EasyBatch\Interfaces\BatchItemInterface $batchItem */
        $batchItem = $this->doFind($batchItemId);

        if ($batchItem !== null) {
            return $batchItem;
        }

        throw new BatchItemNotFoundException(\sprintf('BatchItem for id "%s" not found', $batchItemId));
    }

    public function paginateItems(
        PaginationInterface $pagination,
        int|string $batchId,
        ?string $dependsOnName = null
    ): LengthAwarePaginatorInterface {
        $paginator = new DoctrineDbalLengthAwarePaginator($pagination, $this->conn, $this->table);

        $paginator->setFilterCriteria(
            static function (QueryBuilder $queryBuilder) use ($batchId, $dependsOnName): void {
                $queryBuilder
                    ->where('batch_id = :batchId')
                    ->setParameter('batchId', $batchId);

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
            }
        );

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

    public function paginateItemsForDispatch(
        PaginationInterface $pagination,
        int|string $batchId,
        ?string $dependsOnName = null
    ): LengthAwarePaginatorInterface {
        /** @var \EonX\EasyPagination\Paginators\DoctrineDbalLengthAwarePaginator $paginator */
        $paginator = $this->paginateItems($pagination, $batchId, $dependsOnName);

        $paginator->addFilterCriteria(static function (QueryBuilder $queryBuilder): void {
            // Dispatch only pending items
            $queryBuilder
                ->andWhere('status = :createdStatus')
                ->setParameter('createdStatus', BatchObjectInterface::STATUS_CREATED);
        });

        return $paginator;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function save(BatchItemInterface $batchItem): BatchItemInterface
    {
        $this->doSave($batchItem);

        return $batchItem;
    }

    /**
     * @param \EonX\EasyBatch\Interfaces\BatchItemInterface[] $batchItems
     *
     * @throws \Doctrine\DBAL\Exception
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    public function updateStatusToPending(array $batchItems): void
    {
        $count = \count($batchItems);

        if ($count < 1) {
            return;
        }

        $batchItemIds = \array_map(static function (BatchItemInterface $batchItem): int|string {
            return $batchItem->getIdOrFail();
        }, $batchItems);

        $queryBuilder = $this->conn->createQueryBuilder();
        $queryBuilder
            ->update($this->table)
            ->set('status', ':statusPending')
            ->where($queryBuilder->expr()->{$count === 1 ? 'eq' : 'in'}('id', ':batchItemIds'))
            ->andWhere('status = :statusCreated')
            ->setParameter(
                'batchItemIds',
                $count === 1 ? $batchItemIds[0] : $batchItemIds,
                $count === 1 ? Types::STRING : Types::ARRAY
            )
            ->setParameter('statusPending', BatchObjectInterface::STATUS_PENDING, Types::STRING)
            ->setParameter('statusCreated', BatchObjectInterface::STATUS_CREATED, Types::STRING);

        $this->conn->executeStatement(
            $queryBuilder->getSQL(),
            $queryBuilder->getParameters(),
            $queryBuilder->getParameterTypes()
        );

        foreach ($batchItems as $batchItem) {
            $batchItem->setStatus(BatchObjectInterface::STATUS_PENDING);
        }
    }
}
