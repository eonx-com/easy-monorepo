<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Doctrine\Result;

use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Driver\Result;
use OpenSwoole\Core\Coroutine\Client\PDOStatementProxy;
use PDO;
use PDOException;

final readonly class DbalResult implements Result
{
    public function __construct(
        private PDOStatementProxy $pdoStatement,
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
        /** @var list<array<string, mixed>> $result */
        $result = $this->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public function fetchAllNumeric(): array
    {
        /** @var list<list<mixed>> $result */
        $result = $this->fetchAll(PDO::FETCH_NUM);

        return $result;
    }

    public function fetchAssociative(): false|array
    {
        return $this->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchFirstColumn(): array
    {
        /** @var list<mixed> $result */
        $result = $this->fetchAll(PDO::FETCH_COLUMN);

        return $result;
    }

    public function fetchNumeric(): false|array
    {
        /** @var list<mixed>|false $result */
        $result = $this->fetch(PDO::FETCH_NUM);

        return $result;
    }

    public function fetchOne(): mixed
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
     * @return false|array
     *
     * @throws \Doctrine\DBAL\Driver\PDO\Exception
     */
    private function fetch(int $mode): mixed
    {
        try {
            /** @var false|array $result */
            $result = $this->pdoStatement->fetch($mode);

            return $result;
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
