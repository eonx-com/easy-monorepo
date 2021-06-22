<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;

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
        $this->batchesTable = $batchesTable ?? BatchRepositoryInterface::DEFAULT_TABLE;
        $this->batchItemsTable = $batchItemsTable ?? BatchItemRepositoryInterface::DEFAULT_TABLE;
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
        $batchesTable->addColumn('failed', 'integer');
        $batchesTable->addColumn('succeeded', 'integer');
        $batchesTable->addColumn('processed', 'integer');
        $batchesTable->addColumn('total', 'integer');
        $batchesTable->addColumn('parent_batch_item_id', 'guid', [
            'notNull' => false,
        ]);

        $batchItemsTable = $schema->createTable($this->batchItemsTable);
        $batchItemsTable->addColumn('batch_id', 'guid');
        $batchItemsTable->addColumn('attempts', 'integer');
        $batchItemsTable->addColumn('requires_approval', 'integer');
        $batchItemsTable->addColumn('message', 'text', [
            'notNull' => false,
        ]);
        $batchItemsTable->addColumn('depends_on_name', 'string', [
            'length' => 191,
            'notNull' => false,
        ]);

        $this->addSharedColumns($batchesTable);
        $this->addSharedColumns($batchItemsTable);

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

    private function addSharedColumns(Table $table): void
    {
        $table->addColumn('id', 'guid');
        $table->setPrimaryKey(['id']);

        $table->addColumn('class', 'string', [
            'length' => 191,
        ]);

        $table->addColumn('name', 'string', [
            'length' => 191,
            'notNull' => false,
        ]);

        $table->addColumn('status', 'string', [
            'length' => 50,
        ]);

        $table->addColumn('throwable', 'text', [
            'notNull' => false,
        ]);

        $table->addColumn('type', 'string', [
            'length' => 191,
            'notNull' => false,
        ]);

        $table->addColumn('cancelled_at', 'datetime', [
            'notNull' => false,
        ]);

        $table->addColumn('started_at', 'datetime', [
            'notNull' => false,
        ]);

        $table->addColumn('finished_at', 'datetime', [
            'notNull' => false,
        ]);

        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
    }
}
