<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;

abstract class AbstractDoctrineDbalTestCase extends AbstractTestCase
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $conn;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function addItemToTable(Connection $conn, string $title): void
    {
        $conn->insert('items', ['title' => $title]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function addParentToTable(Connection $conn, string $title, int $itemId): void
    {
        $conn->insert('parents', ['parent_title' => $title, 'item_id' => $itemId]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function createItemsTable(Connection $conn): void
    {
        $schema = new Schema();

        $table = $schema->createTable('items');
        $table
            ->addColumn('id', 'integer')
            ->setAutoincrement(true);

        $table
            ->addColumn('title', 'string', ['length' => 191])
            ->setNotnull(false);

        $table->setPrimaryKey(['id']);

        foreach ($schema->toSql($conn->getDatabasePlatform()) as $sql) {
            $conn->executeStatement($sql);
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function createParentsTable(Connection $conn): void
    {
        $schema = new Schema();

        $table = $schema->createTable('parents');

        $table
            ->addColumn('id', 'integer')
            ->setAutoincrement(true);

        $table
            ->addColumn('parent_title', 'string', ['length' => 191])
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
    protected function getDoctrineDbalConnection(): Connection
    {
        if ($this->conn !== null) {
            return $this->conn;
        }

        return $this->conn = DriverManager::getConnection([
            'url' => 'sqlite:///:memory:',
        ]);
    }
}
