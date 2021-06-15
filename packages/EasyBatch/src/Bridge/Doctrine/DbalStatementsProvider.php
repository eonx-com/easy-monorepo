<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use EonX\EasyAsync\Interfaces\Batch\BatchItemStoreInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchStoreInterface;

final class DbalStatementsProvider
{
    /**
     * @var string
     */
    private $batchItemsTable;

    /**
     * @var string
     */
    private $batchesTable;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $conn;

    /**
     * @var callable
     */
    private $extendBatchItemsTable;

    /**
     * @var callable
     */
    private $extendBatchesTable;

    public function __construct(Connection $conn, ?string $batchesTable = null, ?string $batchItemsTable = null)
    {
        $this->conn = $conn;
        $this->batchesTable = $batchesTable ?? BatchStoreInterface::DEFAULT_TABLE;
        $this->batchItemsTable = $batchItemsTable ?? BatchItemStoreInterface::DEFAULT_TABLE;
    }

    public function extendBatchItemsTable(callable $callable): self
    {
        $this->extendBatchItemsTable = $callable;

        return $this;
    }

    public function extendBatchesTable(callable $callable): self
    {
        $this->extendBatchesTable = $callable;

        return $this;
    }

    /**
     * @return string[]
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function migrateStatements(): array
    {
        $schema = new Schema();

        $batchesTable = $schema->createTable($this->batchesTable);
        $batchesTable->addColumn('id', 'guid');
        $batchesTable->addColumn('class', 'string', [
            'length' => 191,
        ]);
        $batchesTable->addColumn('failed', 'integer');
        $batchesTable->addColumn('succeeded', 'integer');
        $batchesTable->addColumn('processed', 'integer');
        $batchesTable->addColumn('total', 'integer');
        $batchesTable->addColumn('status', 'string', [
            'length' => 50,
        ]);
        $batchesTable->addColumn('name', 'string', [
            'notNull' => false,
        ]);
        $batchesTable->addColumn('cancelled_at', 'datetime', [
            'notNull' => false,
        ]);
        $batchesTable->addColumn('started_at', 'datetime', [
            'notNull' => false,
        ]);
        $batchesTable->addColumn('finished_at', 'datetime', [
            'notNull' => false,
        ]);
        $batchesTable->addColumn('throwable', 'text', [
            'notNull' => false,
        ]);
        $batchesTable->addColumn('batch_item_id', 'guid', [
            'notNull' => false,
        ]);
        $batchesTable->addColumn('created_at', 'datetime');
        $batchesTable->addColumn('updated_at', 'datetime');
        $batchesTable->setPrimaryKey(['id']);

        $batchItemsTable = $schema->createTable($this->batchItemsTable);
        $batchItemsTable->addColumn('id', 'guid');
        $batchItemsTable->addColumn('batch_id', 'guid');
        $batchItemsTable->addColumn('target_class', 'string', [
            'length' => 191,
        ]);
        $batchItemsTable->addColumn('status', 'string', [
            'length' => 50,
        ]);
        $batchItemsTable->addColumn('started_at', 'datetime');
        $batchItemsTable->addColumn('finished_at', 'datetime');
        $batchItemsTable->addColumn('attempts', 'integer');
        $batchItemsTable->addColumn('reason', 'string', [
            'length' => 191,
            'notNull' => false,
        ]);
        $batchItemsTable->addColumn('reason_params', 'text', [
            'notNull' => false,
        ]);
        $batchItemsTable->addColumn('throwable', 'text', [
            'notNull' => false,
        ]);
        $batchItemsTable->addColumn('created_at', 'datetime');
        $batchItemsTable->addColumn('updated_at', 'datetime');
        $batchItemsTable->setPrimaryKey(['id']);

        if ($this->extendBatchesTable !== null) {
            \call_user_func($this->extendBatchesTable, $batchesTable);
        }

        if ($this->extendBatchItemsTable !== null) {
            \call_user_func($this->extendBatchItemsTable, $batchItemsTable);
        }

        return $schema->toSql($this->conn->getDatabasePlatform());
    }

    /**
     * @return string[]
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function rollbackStatements(): array
    {
        return [
            \sprintf('DROP TABLE %s;', $this->batchItemsTable),
            \sprintf('DROP TABLE %s;', $this->batchesTable),
        ];
    }
}
