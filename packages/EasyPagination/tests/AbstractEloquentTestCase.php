<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests;

use Doctrine\DBAL\DriverManager;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Database\SQLiteConnection;

abstract class AbstractEloquentTestCase extends AbstractTestCase
{
    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    private $conn;

    protected static function createItemsTable(Model $model): void
    {
        $schema = new SQLiteBuilder($model->getConnection());

        $schema->create('items', static function (Blueprint $table): void {
            $table->integer('id', true);
            $table->string('title')
                ->nullable(true);
        });
    }

    protected static function createParentsTable(Model $model): void
    {
        $schema = new SQLiteBuilder($model->getConnection());

        $schema->create('parents', static function (Blueprint $table): void {
            $table->integer('id', true);
            $table->string('title')
                ->nullable(true);
            $table->integer('item_id', false, true);

            $table->foreign('item_id')
                ->references('id')
                ->on('items');
        });
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getEloquentConnection(): ConnectionInterface
    {
        if ($this->conn !== null) {
            return $this->conn;
        }

        $doctrineConn = DriverManager::getConnection([
            'url' => 'sqlite:///:memory:',
        ]);

        /** @var \PDO $pdo */
        $pdo = $doctrineConn->getWrappedConnection();

        $conn = new SQLiteConnection($pdo);
        $conn->setSchemaGrammar(new SQLiteGrammar());

        return $this->conn = $conn;
    }
}
