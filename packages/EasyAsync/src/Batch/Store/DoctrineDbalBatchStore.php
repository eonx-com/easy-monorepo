<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Batch\Store;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use EonX\EasyAsync\Exceptions\Batch\BatchIdRequiredException;
use EonX\EasyAsync\Exceptions\Batch\BatchNotFoundException;
use EonX\EasyAsync\Interfaces\Batch\BatchFactoryInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchStoreInterface;

final class DoctrineDbalBatchStore extends AbstractDoctrineDbalStore implements BatchStoreInterface
{
    /**
     * @var string
     */
    private const SAVEPOINT = 'easy_async_batch_savepoint';

    /**
     * @var \EonX\EasyAsync\Interfaces\Batch\BatchFactoryInterface
     */
    private $batchFactory;

    /**
     * @var bool
     */
    private $savepointActive = false;

    public function __construct(BatchFactoryInterface $batchFactory, Connection $conn, ?string $table = null)
    {
        $this->batchFactory = $batchFactory;

        parent::__construct($conn, $table ?? self::DEFAULT_TABLE);
    }

    public function cancelUpdate(): void
    {
        $this->savepointActive
            ? $this->conn->rollbackSavepoint(self::SAVEPOINT)
            : $this->conn->rollBack();
    }

    public function find(string $batchId): ?BatchInterface
    {
        $sql = \sprintf('SELECT * FROM %s WHERE id = :id', $this->table);

        $data = $this->conn->fetchAssociative($sql, [
            'id' => $batchId,
        ]);

        return \is_array($data) ? $this->batchFactory->instantiateFromArray($data) : null;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \EonX\EasyAsync\Exceptions\Batch\BatchNotFoundException
     * @throws \Throwable
     */
    public function findForUpdate(string $batchId): BatchInterface
    {
        $sql = \sprintf('SELECT * FROM %s WHERE id = :id FOR UPDATE', $this->table);

        $data = $this->conn->fetchAssociative($sql, [
            'id' => $batchId,
        ]);

        if (\is_array($data) === false) {
            throw new BatchNotFoundException(\sprintf('Batch "%s" not found for update', $batchId));
        }

        return $this->batchFactory->instantiateFromArray($data);
    }

    public function finishUpdate(): void
    {
        $this->savepointActive
            ? $this->conn->releaseSavepoint(self::SAVEPOINT)
            : $this->conn->commit();
    }

    public function startUpdate(): void
    {
        // If transaction active and savepoint supported, create new savepoint
        if ($this->conn->isTransactionActive() && $this->conn->getDatabasePlatform()->supportsSavepoints()) {
            $this->conn->createSavepoint(self::SAVEPOINT);
            $this->savepointActive = true;

            return;
        }

        // Otherwise create transaction
        $this->savepointActive = false;
        $this->conn->beginTransaction();
    }

    public function store(BatchInterface $batch): BatchInterface
    {
        if ($batch->getId() === null) {
            throw new BatchIdRequiredException('Batch ID is required to store it.');
        }

        $exists = $this->existsInDb($batch->getId());

        return $exists === false
            ? $this->insert($batch)
            : $this->update($batch);
    }

    public function update(BatchInterface $batch): BatchInterface
    {
        $batch->setUpdatedAt(Carbon::now('UTC'));

        $this->conn->update($this->table, $this->formatData($batch->toArray()), ['id' => $batch->getId()]);

        return $batch;
    }

    private function insert(BatchInterface $batch): BatchInterface
    {
        $now = Carbon::now('UTC');

        $batch
            ->setCreatedAt($now)
            ->setUpdatedAt($now);

        $this->conn->insert($this->table, $this->formatData($batch->toArray()));

        return $batch;
    }
}
