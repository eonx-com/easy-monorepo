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
     */
    public function findCountsForBatch(int|string $batchId): BatchCountsDto
    {
        $queryBuilder = $this->conn->createQueryBuilder()
            ->select(['status', 'count(id) as _count'])
            ->from($this->table)
            ->where('batch_id = :batchId')
            ->setParameter('batchId', $batchId, \is_string($batchId) ? Types::STRING : Types::INTEGER)
            ->groupBy('status');

        $results = $this->conn->fetchAllAssociative(
            $queryBuilder->getSQL(),
            $queryBuilder->getParameters(),
            $queryBuilder->getParameterTypes()
        );

        $completed = 0;
        $total = 0;
        $results = \array_column($results, '_count', 'status');

        foreach ($results as $status => $count) {
            if (\in_array($status, BatchObjectInterface::STATUSES_FOR_COMPLETED, true)) {
                $completed += $count;
            }

            $total += $count;
        }

        return new BatchCountsDto(
            $results[BatchObjectInterface::STATUS_CANCELLED] ?? 0,
            $results[BatchObjectInterface::STATUS_FAILED] ?? 0,
            $completed,
            $results[BatchObjectInterface::STATUS_SUCCEEDED] ?? 0,
            $total
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \EonX\EasyBatch\Exceptions\BatchItemNotFoundException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
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
        /** @var \EonX\EasyBatch\Interfaces\BatchItemInterface|null $batchItem */
        $batchItem = $this->doFind($batchItemId);

        if ($batchItem !== null) {
            return $batchItem;
        }

        throw new BatchItemNotFoundException(\sprintf('BatchItem for id "%s" not found', $batchItemId));
    }

    public function paginateItems(
        PaginationInterface $pagination,
        int|string $batchId,
        ?string $dependsOnName = null,
    ): LengthAwarePaginatorInterface {
        $paginator = new DoctrineDbalLengthAwarePaginator($pagination, $this->conn, $this->table);

        $paginator->setFilterCriteria(
            static function (QueryBuilder $queryBuilder) use ($batchId, $dependsOnName): void {
                $queryBuilder
                    ->where('batch_id = :batchId')
                    ->setParameter('batchId', $batchId, \is_string($batchId) ? Types::STRING : Types::INTEGER);

                // Make sure to get only batchItems with no dependency
                if ($dependsOnName === null) {
                    $queryBuilder->andWhere('depends_on_name is null');
                }

                // Make sure to get only batchItems for given dependency
                if ($dependsOnName !== null) {
                    $queryBuilder
                        ->andWhere('depends_on_name = :dependsOnName')
                        ->setParameter('dependsOnName', $dependsOnName, Types::STRING);
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

        $batchItemIds = \array_map(
            static fn (BatchItemInterface $batchItem): int|string => $batchItem->getIdOrFail(),
            $batchItems
        );

        $queryBuilder = $this->conn->createQueryBuilder();
        $queryBuilder
            ->update($this->table)
            ->set('status', ':statusPending')
            ->where('status = :statusCreated')
            ->setParameter('statusPending', BatchObjectInterface::STATUS_PENDING, Types::STRING)
            ->setParameter('statusCreated', BatchObjectInterface::STATUS_CREATED, Types::STRING);

        // Handle 1 batchItem
        if ($count === 1) {
            $queryBuilder
                ->andWhere('id = :batchItemId')
                ->setParameter('batchItemId', $batchItemIds[0], Types::STRING);
        }

        // Handle more than 1 batchItem
        if ($count > 1) {
            $batchItemIds = \array_map(
                fn (string $batchItemId): string => $this->conn->quote($batchItemId),
                $batchItemIds
            );

            $queryBuilder->andWhere($queryBuilder->expr()->in('id', $batchItemIds));
        }

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
