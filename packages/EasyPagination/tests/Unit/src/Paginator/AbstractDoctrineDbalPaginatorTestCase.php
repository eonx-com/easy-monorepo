<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Unit\Paginator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use EonX\EasyPagination\Tests\Unit\AbstractUnitTestCase;

abstract class AbstractDoctrineDbalPaginatorTestCase extends AbstractUnitTestCase
{
    protected ?Connection $connection = null;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected static function addChildItemToTable(Connection $connection, string $title, int $itemId): void
    {
        $connection->insert('child_items', [
            'child_title' => $title,
            'item_id' => $itemId,
        ]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected static function addItemToTable(Connection $connection, string $title): void
    {
        $connection->insert('items', ['title' => $title]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected static function createChildItemsTable(Connection $connection): void
    {
        $schema = new Schema();

        $table = $schema->createTable('child_items');

        $table
            ->addColumn('id', 'integer')
            ->setAutoincrement(true);

        $table
            ->addColumn('child_title', 'string', ['length' => 255])
            ->setNotnull(false);

        $table
            ->addColumn('item_id', 'integer');

        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('items', ['item_id'], ['id']);

        foreach ($schema->toSql($connection->getDatabasePlatform()) as $sql) {
            $connection->executeStatement($sql);
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected static function createItemsTable(Connection $connection): void
    {
        $schema = new Schema();

        $table = $schema->createTable('items');
        $table
            ->addColumn('id', 'integer')
            ->setAutoincrement(true);

        $table
            ->addColumn('title', 'string', ['length' => 255])
            ->setNotnull(false);

        $table->setPrimaryKey(['id']);

        foreach ($schema->toSql($connection->getDatabasePlatform()) as $sql) {
            $connection->executeStatement($sql);
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getDoctrineDbalConnection(): Connection
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        $this->connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        return $this->connection;
    }
}
