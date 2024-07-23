<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Doctrine\Provider;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

final readonly class DoctrineDbalStatementProvider
{
    public function __construct(
        private Connection $connection,
        private string $activityLogsTable,
    ) {
    }

    /**
     * @return string[]
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function migrateStatements(): array
    {
        $schema = new Schema();

        $activityLogsTable = $schema->createTable($this->activityLogsTable);
        $activityLogsTable->addColumn('id', 'guid');
        $activityLogsTable->addColumn('actor_type', 'string', [
            'length' => 255,
        ]);
        $activityLogsTable->addColumn('actor_id', 'string', [
            'length' => 255,
            'notNull' => false,
        ]);
        $activityLogsTable->addColumn('actor_name', 'string', [
            'length' => 255,
            'notNull' => false,
        ]);
        $activityLogsTable->addColumn('action', 'string', [
            'length' => 255,
        ]);
        $activityLogsTable->addColumn('subject_type', 'string', [
            'length' => 255,
        ]);
        $activityLogsTable->addColumn('subject_id', 'string', [
            'length' => 255,
        ]);
        $activityLogsTable->addColumn('subject_data', 'text', [
            'notNull' => false,
        ]);
        $activityLogsTable->addColumn('subject_old_data', 'text', [
            'notNull' => false,
        ]);
        $activityLogsTable->addColumn('created_at', 'datetime');
        $activityLogsTable->addColumn('updated_at', 'datetime');
        $activityLogsTable->setPrimaryKey(['id']);

        return $schema->toSql($this->connection->getDatabasePlatform());
    }

    /**
     * @return string[]
     */
    public function rollbackStatements(): array
    {
        return [
            \sprintf('DROP TABLE %s;', $this->activityLogsTable),
        ];
    }
}
