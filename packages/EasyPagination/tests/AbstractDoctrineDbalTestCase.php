<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;

abstract class AbstractDoctrineDbalTestCase extends AbstractTestCase
{
    protected ?Connection $conn = null;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected static function addChildItemToTable(Connection $conn, string $title, int $itemId): void
    {
        $conn->insert('child_items', [
            'child_title' => $title,
            'item_id' => $itemId,
        ]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected static function addItemToTable(Connection $conn, string $title): void
    {
        $conn->insert('items', ['title' => $title]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected static function createChildItemsTable(Connection $conn): void
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

        foreach ($schema->toSql($conn->getDatabasePlatform()) as $sql) {
            $conn->executeStatement($sql);
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected static function createItemsTable(Connection $conn): void
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

        foreach ($schema->toSql($conn->getDatabasePlatform()) as $sql) {
            $conn->executeStatement($sql);
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getDoctrineDbalConnection(): Connection
    {
        if ($this->conn !== null) {
            return $this->conn;
        }

        $this->conn = DriverManager::getConnection([
            'url' => 'sqlite:///:memory:',
        ]);

        return $this->conn;
    }
}
