<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Doctrine\Statement;

use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;
use EonX\EasySwoole\Doctrine\Result\DbalResult;
use OpenSwoole\Core\Coroutine\Client\PDOStatementProxy;
use PDOException;

final readonly class DbalStatement implements Statement
{
    public function __construct(
        private PDOStatementProxy $pdoStatement,
    ) {
    }

    public function bindValue($param, $value, $type = ParameterType::STRING): void
    {
        try {
            $this->pdoStatement->bindValue($param, $value, $type);
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    public function execute(): Result
    {
        try {
            $this->pdoStatement->execute();
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }

        return new DbalResult($this->pdoStatement);
    }
}
