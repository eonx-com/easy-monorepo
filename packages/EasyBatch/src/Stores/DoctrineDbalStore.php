<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Stores;

use Doctrine\DBAL\Connection;
use EonX\EasyBatch\Interfaces\BatchItemStoreInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchStoreInterface;

final class DoctrineDbalStore implements BatchStoreInterface, BatchItemStoreInterface
{
    /**
     * @var string
     */
    private const SAVEPOINT = 'easy_batch.conn.savepoint';

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $conn;

    /**
     * @var string
     */
    private $dateTimeFormat;

    /**
     * @var string
     */
    private $table;

    /**
     * @var bool
     */
    private $savepointActive = false;

    public function __construct(Connection $conn, string $table, ?string $dateTimeFormat = null)
    {
        $this->conn = $conn;
        $this->table = $table;
        $this->dateTimeFormat = $dateTimeFormat ?? BatchObjectInterface::DATETIME_FORMAT;
    }

    /**
     * @param int|string $id
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function find($id): ?array
    {
        $sql = \sprintf('SELECT id FROM %s WHERE id = :id', $this->table);

        $data = $this->conn->fetchAssociative($sql, \compact('id'));

        return \is_array($data) ? $data : null;
    }

    /**
     * @param int|string $id
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function has($id): bool
    {
        $sql = \sprintf('SELECT id FROM %s WHERE id = :id', $this->table);

        return \is_array($this->conn->fetchAssociative($sql, \compact('id')));
    }

    /**
     * @param mixed[] $data
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function persist(array $data): void
    {
        $this->conn->insert($this->table, $this->formatData($data));
    }

    /**
     * @param int|string $id
     * @param mixed[] $data
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function update($id, array $data): void
    {
        $this->conn->update($this->table, $this->formatData($data), \compact('id'));
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function cancelUpdate(): void
    {
        $this->savepointActive
            ? $this->conn->rollbackSavepoint(self::SAVEPOINT)
            : $this->conn->rollBack();
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function finishUpdate(): void
    {
        $this->savepointActive
            ? $this->conn->releaseSavepoint(self::SAVEPOINT)
            : $this->conn->commit();
    }

    /**
     * @param int|string $batchId
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function lockForUpdate($batchId): void
    {
        $sql = \sprintf('SELECT id FROM %s WHERE id = :id FOR UPDATE', $this->table);

        $this->conn->fetchAssociative($sql, ['id' => $batchId]);
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
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

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    protected function formatData(array $data): array
    {
        return \array_map(static function ($value) {
            if (\is_array($value)) {
                return \json_encode($value);
            }

            if ($value instanceof \DateTimeInterface) {
                return $value->format($this->dateTimeFormat);
            }

            if ($value instanceof \Throwable) {
                return \json_encode([
                    'code' => $value->getCode(),
                    'file' => $value->getFile(),
                    'line' => $value->getLine(),
                    'message' => $value->getMessage(),
                    'trace' => $value->getTraceAsString(),
                ]);
            }

            return $value;
        }, $data);
    }
}
