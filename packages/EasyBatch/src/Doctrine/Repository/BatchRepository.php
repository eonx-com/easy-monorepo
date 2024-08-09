<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Doctrine\Repository;

use Doctrine\DBAL\Platforms\SQLitePlatform;
use EonX\EasyBatch\Common\Exception\BatchNotFoundException;
use EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException;
use EonX\EasyBatch\Common\Repository\BatchRepositoryInterface;
use EonX\EasyBatch\Common\ValueObject\BatchInterface;
use Throwable;

final class BatchRepository extends AbstractBatchObjectRepository implements BatchRepositoryInterface
{
    private const SAVEPOINT = 'easy_batch_conn_savepoint';

    /**
     * @var \EonX\EasyBatch\Common\ValueObject\BatchInterface[]
     */
    private array $cache = [];

    private bool $savepointActive = false;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function find(int|string $id): ?BatchInterface
    {
        if (isset($this->cache[$id])) {
            return $this->cache[$id];
        }

        /** @var \EonX\EasyBatch\Common\ValueObject\BatchInterface|null $batch */
        $batch = $this->doFind($id);

        if ($batch !== null) {
            $this->cache[$id] = $batch;
        }

        return $batch;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \EonX\EasyBatch\Common\Exception\BatchNotFoundException
     */
    public function findNestedOrFail(int|string $parentBatchItemId): BatchInterface
    {
        $sql = \sprintf('SELECT * FROM %s WHERE parent_batch_item_id = :id', $this->table);
        $data = $this->conn->fetchAssociative($sql, ['id' => $parentBatchItemId]);

        if (\is_array($data)) {
            /** @var \EonX\EasyBatch\Common\ValueObject\BatchInterface $batch */
            $batch = $this->factory->createFromArray($data);

            return $batch;
        }

        throw new BatchNotFoundException(\sprintf(
            'Batch for parent_batch_item_id "%s" not found',
            $parentBatchItemId
        ));
    }

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchNotFoundException
     * @throws \Doctrine\DBAL\Exception
     */
    public function findOrFail(int|string $id): BatchInterface
    {
        $batch = $this->find($id);

        if ($batch !== null) {
            return $batch;
        }

        throw new BatchNotFoundException(\sprintf('Batch for id "%s" not found', $id));
    }

    public function reset(): BatchRepositoryInterface
    {
        $this->cache = [];

        return $this;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function save(BatchInterface $batch): BatchInterface
    {
        $this->doSave($batch);

        return $batch;
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Exception
     * @throws \EonX\EasyBatch\Common\Exception\BatchNotFoundException
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
     * @throws \Throwable
     */
    public function updateAtomic(BatchInterface $batch, callable $func): BatchInterface
    {
        if ($batch->getId() === null) {
            throw new BatchObjectIdRequiredException('Batch ID is required to update it.');
        }

        $this->beginTransaction();

        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            if ($this->conn->getDatabasePlatform() instanceof SQLitePlatform === false) {
                $queryBuilder->forUpdate();
            }

            $queryBuilder->select('*')
                ->from($this->table)
                ->where('id = :id');

            $data = $this->conn->fetchAssociative($queryBuilder->getSQL(), ['id' => $batch->getId()]);
            $freshBatch = \is_array($data) ? $this->factory->createFromArray($data) : null;

            if ($freshBatch === null) {
                throw new BatchNotFoundException(\sprintf('Batch for id "%s" not found', $batch->getId()));
            }

            $freshBatch = $this->save($func($freshBatch));

            $this->commit();

            return $freshBatch;
        } catch (Throwable $throwable) {
            $this->rollback();

            throw $throwable;
        }
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
    private function beginTransaction(): void
    {
        // If transaction active and savepoint supported, create new savepoint
        if ($this->conn->isTransactionActive() && $this->conn->getDatabasePlatform()->supportsSavepoints()) {
            $this->conn->createSavepoint(self::SAVEPOINT);
            $this->savepointActive = true;

            return;
        }

        // Otherwise, create transaction
        $this->savepointActive = false;
        $this->conn->beginTransaction();
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     */
    private function commit(): void
    {
        $this->savepointActive
            ? $this->conn->releaseSavepoint(self::SAVEPOINT)
            : $this->conn->commit();
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     */
    private function rollback(): void
    {
        $this->savepointActive
            ? $this->conn->rollbackSavepoint(self::SAVEPOINT)
            : $this->conn->rollBack();
    }
}
