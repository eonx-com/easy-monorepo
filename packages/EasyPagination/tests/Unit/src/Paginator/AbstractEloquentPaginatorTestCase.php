<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Unit\Paginator;

use Doctrine\DBAL\DriverManager;
use EonX\EasyPagination\Tests\Unit\AbstractUnitTestCase;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Database\SQLiteConnection;

abstract class AbstractEloquentPaginatorTestCase extends AbstractUnitTestCase
{
    private ?ConnectionInterface $conn = null;

    protected static function createChildItemsTable(Model $model): void
    {
        $schema = new SQLiteBuilder($model->getConnection());

        $schema->create('child_items', static function (Blueprint $table): void {
            $table->integer('id', true);
            $table->string('child_title')
                ->nullable();
            $table->integer('item_id', false, true);

            $table->foreign('item_id')
                ->references('id')
                ->on('items');
        });
    }

    protected static function createItemsTable(Model $model): void
    {
        $schema = new SQLiteBuilder($model->getConnection());

        $schema->create('items', static function (Blueprint $table): void {
            $table->integer('id', true);
            $table->string('title')
                ->nullable();
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
        $pdo = $doctrineConn->getNativeConnection();

        $this->conn = new SQLiteConnection($pdo);
        $this->conn->setSchemaGrammar(new SQLiteGrammar());

        return $this->conn;
    }
}
