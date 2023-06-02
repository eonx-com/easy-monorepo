<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Repositories;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use EonX\EasyBatch\Exceptions\BatchObjectNotSavedException;
use EonX\EasyBatch\Interfaces\BatchObjectFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectIdStrategyInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchObjectTransformerInterface;

abstract class AbstractBatchObjectRepository
{
    /**
     * @var null|string[]
     */
    private ?array $tableColumns = null;

    public function __construct(
        protected BatchObjectFactoryInterface $factory,
        protected BatchObjectIdStrategyInterface $idStrategy,
        protected BatchObjectTransformerInterface $transformer,
        protected Connection $conn,
        protected string $table
    ) {
        // No body needed.
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function doFind(int|string $id): ?BatchObjectInterface
    {
        $data = $this->fetchData($id);

        return $data !== null ? $this->factory->createFromArray($data) : null;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectNotSavedException
     */
    protected function doSave(BatchObjectInterface $batchObject): void
    {
        $batchObjectId = $batchObject->getId() ?? $this->idStrategy->generateId();
        $now = Carbon::now('UTC');

        $batchObject->setId($batchObjectId);
        $batchObject->setCreatedAt($batchObject->getCreatedAt() ?? $now);
        $batchObject->setUpdatedAt($now);

        $data = $this->transformer->transformToArray($batchObject);
        foreach (\array_diff(\array_keys($data), $this->resolveTableColumns()) as $toRemove) {
            unset($data[$toRemove]);
        }

        $batchObjectExists = $this->has($batchObjectId);
        $affectedRows = (int)($batchObjectExists === false
            ? $this->conn->insert($this->table, $data)
            : $this->conn->update($this->table, $data, ['id' => $batchObjectId]));

        // This logic should affect only one row, otherwise something went wrong
        if ($affectedRows !== 1) {
            throw new BatchObjectNotSavedException(\sprintf(
                '%s with id %s was not %s in database',
                \get_class($batchObject),
                $batchObjectId,
                $batchObjectExists ? 'updated' : 'inserted'
            ));
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function has(int|string $id): bool
    {
        return \is_array($this->fetchData($id));
    }

    /**
     * @return null|mixed[]
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchData(int|string $id): ?array
    {
        $sql = \sprintf('SELECT * FROM %s WHERE id = :id', $this->table);
        $result = $this->conn->fetchAssociative($sql, ['id' => $id]);

        return \is_array($result) ? $result : null;
    }

    /**
     * @return string[]
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function resolveTableColumns(): array
    {
        if ($this->tableColumns !== null) {
            return $this->tableColumns;
        }

        $sql = $this->conn->getDatabasePlatform()
            ->getListTableColumnsSQL($this->table);

        $columns = $this->conn->fetchAllAssociative($sql);

        $nameColumnKey = 'name';

        if (\is_a($this->conn->getDatabasePlatform(), PostgreSQLPlatform::class)) {
            $nameColumnKey = 'field';
        }

        if (
            \is_a($this->conn->getDatabasePlatform(), '\Doctrine\DBAL\Platforms\MySqlPlatform')
            || \is_a($this->conn->getDatabasePlatform(), '\Doctrine\DBAL\Platforms\AbstractMySQLPlatform')
        ) {
            $nameColumnKey = 'Field';
        }

        return $this->tableColumns = \array_column($columns, $nameColumnKey);
    }
}
