<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Batch\Store;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use EonX\EasyAsync\Batch\Batch;
use EonX\EasyAsync\Exceptions\Batch\BatchIdRequiredException;
use EonX\EasyAsync\Exceptions\Batch\BatchNotFoundException;
use EonX\EasyAsync\Interfaces\Batch\BatchInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchStoreInterface;

final class DoctrineDbalBatchStore extends AbstractDoctrineDbalStore implements BatchStoreInterface
{
    /**
     * @var string
     */
    private const SAVEPOINT = 'easy_async_batch_savepoint';

    /**
     * @var bool
     */
    private $savepointActive = false;

    public function __construct(Connection $conn, ?string $table = null)
    {
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

        return \is_array($data) ? $this->instantiateBatch($data, $batchId) : null;
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

        return $this->instantiateBatch($data, $batchId);
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
        $data = $batch->toArray();
        $now = Carbon::now('UTC');

        // Always set updated_at
        $data['updated_at'] = $now;

        // New batch item, insert
        if ($exists === false) {
            // Set created_at on new batch item only
            $data['created_at'] = $now;

            $this->conn->insert($this->table, $this->formatData($data));

            return $batch;
        }

        // Existing batch item, update
        $this->conn->update($this->table, $this->formatData($data), ['id' => $batch->getId()]);

        return $batch;
    }

    public function storeForUpdate(BatchInterface $batch): BatchInterface
    {
        $this->conn->update($this->table, $this->formatData($batch->toArray()), ['id' => $batch->getId()]);
        $this->conn->commit();

        return $batch;
    }

    /**
     * @param mixed[] $data
     */
    private function instantiateBatch(array $data, string $batchId): BatchInterface
    {
        $batch = (new Batch())
            ->setFailed((int)($data['failed'] ?? 0))
            ->setId($data['id'] ?? $batchId)
            ->setProcessed((int)($data['processed'] ?? 0))
            ->setStatus($data['status'] ?? BatchInterface::STATUS_PENDING)
            ->setSucceeded((int)($data['succeeded'] ?? 0))
            ->setTotal((int)($data['total'] ?? 0));

        if (isset($data['finished_at'])) {
            $finishedAt = Carbon::createFromFormat(self::DATETIME_FORMAT, $data['finished_at']);

            if ($finishedAt instanceof Carbon) {
                $batch->setFinishedAt($finishedAt);
            }
        }

        if (isset($data['started_at'])) {
            $startedAt = Carbon::createFromFormat(self::DATETIME_FORMAT, $data['started_at']);

            if ($startedAt instanceof Carbon) {
                $batch->setFinishedAt($startedAt);
            }
        }

        return $batch;
    }
}
