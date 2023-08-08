<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO;

use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Driver\PDO\ParameterTypeMap;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;
use OpenSwoole\Core\Coroutine\Client\PDOStatementProxy;
use PDOException;

final class DbalStatement implements Statement
{
    public function __construct(
        private readonly PDOStatementProxy $pdoStatement,
    ) {
    }

    public function bindParam(
        $param,
        &$variable,
        $type = ParameterType::STRING,
        $length = null,
        ?array $driverOptions = null,
    ): bool {
        try {
            return $this->pdoStatement->bindParam(
                $param,
                $variable,
                ParameterTypeMap::convertParamType($type),
                $length ?? 0,
                ...\array_slice(\func_get_args(), 4),
            );
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    public function bindValue($param, $value, $type = ParameterType::STRING): bool
    {
        try {
            return $this->pdoStatement->bindValue($param, $value, ParameterTypeMap::convertParamType($type));
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    public function execute($params = null): Result
    {
        try {
            $this->pdoStatement->execute($params);
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }

        return new DbalResult($this->pdoStatement);
    }
}
