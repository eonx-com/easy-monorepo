<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Doctrine\Result;

use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Driver\Result;
use OpenSwoole\Core\Coroutine\Client\PDOStatementProxy;
use PDO;
use PDOException;

final class DbalResult implements Result
{
    public function __construct(
        private readonly PDOStatementProxy $pdoStatement,
    ) {
    }

    public function columnCount(): int
    {
        try {
            return $this->pdoStatement->columnCount();
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    public function fetchAllAssociative(): array
    {
        return $this->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchAllNumeric(): array
    {
        return $this->fetchAll(PDO::FETCH_NUM);
    }

    public function fetchAssociative()
    {
        return $this->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchFirstColumn(): array
    {
        return $this->fetchAll(PDO::FETCH_COLUMN);
    }

    public function fetchNumeric()
    {
        return $this->fetch(PDO::FETCH_NUM);
    }

    public function fetchOne()
    {
        return $this->fetch(PDO::FETCH_COLUMN);
    }

    public function free(): void
    {
        $this->pdoStatement->closeCursor();
    }

    public function rowCount(): int
    {
        try {
            return $this->pdoStatement->rowCount();
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    /**
     * @throws \Doctrine\DBAL\Driver\PDO\Exception
     */
    private function fetch(int $mode): mixed
    {
        try {
            return $this->pdoStatement->fetch($mode);
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    /**
     * @throws \Doctrine\DBAL\Driver\PDO\Exception
     */
    private function fetchAll(int $mode): array
    {
        try {
            return $this->pdoStatement->fetchAll($mode);
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }
}
