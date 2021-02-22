<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Batch\Store;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use EonX\EasyAsync\Batch\Batch;
use EonX\EasyAsync\Exceptions\Batch\BatchIdRequiredException;
use EonX\EasyAsync\Exceptions\Batch\BatchNotFoundException;
use EonX\EasyAsync\Interfaces\Batch\BatchInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchItemInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchStoreInterface;

final class DoctrineDbalBatchStore extends AbstractDoctrineDbalStore implements BatchStoreInterface
{
    public function __construct(Connection $conn, ?string $table = null)
    {
        parent::__construct($conn, $table ?? 'easy_async_batches');
    }

    public function find(string $batchId): ?BatchInterface
    {
        $sql = \sprintf('SELECT * FROM %s WHERE id = :id', $this->getTableForQuery());

        $data = $this->conn->fetchAssociative($sql, [
            'id' => $batchId,
        ]);

        return \is_array($data) ? $this->instantiateBatch($data, $batchId) : null;
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

    public function updateForItem(BatchInterface $batch, BatchItemInterface $batchItem): BatchInterface
    {
        if ($batch->getId() === null) {
            throw new BatchIdRequiredException('Batch ID is required to store it.');
        }

        // We lock the batch to handle concurrency
        $this->conn->beginTransaction();

        try {
            $batchData = $this->getBatchEssentialData($batch->getId());
            $batch->setProcessed($batchData['processed']++);

            switch ($batchItem->getStatus()) {
                case BatchItemInterface::STATUS_FAILED:
                    $batch->setFailed($batchData['failed']++);
                    break;
                case BatchItemInterface::STATUS_SUCCESS:
                    $batch->setSucceeded($batchData['succeeded']++);
            }

            // Start the batch timer
            if ($batch->getStartedAt() === null) {
                $batch->setStartedAt(Carbon::now('UTC'));
                $batch->setStatus(BatchInterface::STATUS_PROCESSING);
            }

            // Last item of the batch
            if ($batchData['total'] === $batchData['processed']) {
                $batch->setFinishedAt(Carbon::now('UTC'));
                $batch->setStatus($batch->countFailed() > 0 ? BatchInterface::STATUS_FAILED : BatchInterface::STATUS_SUCCESS);
            }

            $this->conn->update($this->table, $this->formatData($batch->toArray()), ['id' => $batch->getId()]);
            $this->conn->commit();
        } catch (\Throwable $throwable) {
            $this->conn->rollBack();

            throw $throwable;
        }

        return $batch;
    }

    /**
     * @return mixed[]
     *
     * @throws \Doctrine\DBAL\Exception
     * @throws \EonX\EasyAsync\Exceptions\Batch\BatchNotFoundException
     */
    private function getBatchEssentialData(string $batchId): array
    {
        $sql = \sprintf(
            'SELECT failed, processed, succeeded, total, status FROM %s WHERE id = :id FOR UPDATE',
            $this->getTableForQuery()
        );

        $data = $this->conn->fetchAssociative($sql, [
            'id' => $batchId,
        ]);

        if (\is_array($data)) {
            return $data;
        }

        throw new BatchNotFoundException(\sprintf('Batch "%s" not found for update', $batchId));
    }

    /**
     * @param mixed[] $data
     */
    private function instantiateBatch(array $data, string $batchId): BatchInterface
    {
        $batch = (new Batch())
            ->setFailed($data['failed'] ?? 0)
            ->setId($data['id'] ?? $batchId)
            ->setProcessed($data['processed'] ?? 0)
            ->setStatus($data['status'] ?? BatchInterface::STATUS_PENDING)
            ->setSucceeded($data['succeeded'])
            ->setTotal($data['total']);

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
