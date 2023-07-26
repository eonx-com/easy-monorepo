<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Doctrine;

use Closure;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use EonX\EasyWebhook\Interfaces\Stores\ResultStoreInterface;
use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;

final class DbalStatementsProvider
{
    private ?Closure $extendWebhookResultsTable = null;

    private ?Closure $extendWebhooksTable = null;

    private string $webhookResultsTable;

    private string $webhooksTable;

    public function __construct(
        private Connection $conn,
        ?string $webhooksTable = null,
        ?string $webhookResultsTable = null,
    ) {
        $this->webhooksTable = $webhooksTable ?? StoreInterface::DEFAULT_TABLE;
        $this->webhookResultsTable = $webhookResultsTable ?? ResultStoreInterface::DEFAULT_TABLE;
    }

    public function extendWebhookResultsTable(callable $callable): self
    {
        $this->extendWebhookResultsTable = $callable(...);

        return $this;
    }

    public function extendWebhooksTable(callable $callable): self
    {
        $this->extendWebhooksTable = $callable(...);

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

        $webhooksTable = $schema->createTable($this->webhooksTable);
        $webhooksTable->addColumn('id', 'guid');
        $webhooksTable->addColumn('class', 'string', [
            'length' => 191,
        ]);
        $webhooksTable->addColumn('method', 'string', [
            'length' => 10,
        ]);
        $webhooksTable->addColumn('url', 'string', [
            'length' => 191,
        ]);
        $webhooksTable->addColumn('status', 'string', [
            'length' => 50,
        ]);
        $webhooksTable->addColumn('event', 'string', [
            'length' => 191,
            'notNull' => false,
        ]);
        $webhooksTable->addColumn('http_options', 'text', [
            'notNull' => false,
        ]);
        $webhooksTable->addColumn('current_attempt', 'integer', [
            'default' => 0,
            'length' => 11,
        ]);
        $webhooksTable->addColumn('max_attempt', 'integer', [
            'default' => 1,
            'length' => 11,
        ]);
        $webhooksTable->addColumn('send_after', 'datetime', [
            'notNull' => false,
        ]);
        $webhooksTable->addColumn('created_at', 'datetime');
        $webhooksTable->addColumn('updated_at', 'datetime');
        $webhooksTable->setPrimaryKey(['id']);
        $webhooksTable->addIndex(['status', 'send_after'], 'send_after_idx');

        $webhookResultsTable = $schema->createTable($this->webhookResultsTable);
        $webhookResultsTable->addColumn('id', 'guid');
        $webhookResultsTable->addColumn('method', 'string', [
            'length' => 10,
        ]);
        $webhookResultsTable->addColumn('url', 'string');
        $webhookResultsTable->addColumn('http_options', 'text', [
            'notNull' => false,
        ]);
        $webhookResultsTable->addColumn('response', 'text', [
            'notNull' => false,
        ]);
        $webhookResultsTable->addColumn('throwable', 'text', [
            'notNull' => false,
        ]);
        $webhookResultsTable->addColumn('webhook_class', 'string', [
            'length' => 191,
        ]);
        $webhookResultsTable->addColumn('webhook_id', 'guid');
        $webhookResultsTable->addColumn('created_at', 'datetime');
        $webhookResultsTable->addColumn('updated_at', 'datetime');
        $webhookResultsTable->setPrimaryKey(['id']);

        if ($this->extendWebhooksTable !== null) {
            \call_user_func($this->extendWebhooksTable, $webhooksTable);
        }

        if ($this->extendWebhookResultsTable !== null) {
            \call_user_func($this->extendWebhookResultsTable, $webhookResultsTable);
        }

        return $schema->toSql($this->conn->getDatabasePlatform());
    }

    /**
     * @return string[]
     */
    public function rollbackStatements(): array
    {
        return [
            \sprintf('DROP TABLE %s;', $this->webhookResultsTable),
            \sprintf('DROP TABLE %s;', $this->webhooksTable),
        ];
    }
}
