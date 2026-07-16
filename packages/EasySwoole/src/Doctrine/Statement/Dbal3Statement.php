<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Doctrine\Statement;

use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;
use EonX\EasySwoole\Doctrine\Result\DbalResult;
use OpenSwoole\Core\Coroutine\Client\PDOStatementProxy;
use PDO;
use PDOException;

/**
 * @deprecated Remove when Doctrine DBAL 3 support is dropped.
 */
final readonly class Dbal3Statement implements Statement
{
    public function __construct(
        private PDOStatementProxy $pdoStatement,
    ) {}

    public function bindParam($param, &$variable, $type = ParameterType::STRING, $length = null): bool
    {
        try {
            return $this->pdoStatement->bindParam($param, $variable, $this->convertParamType($type), $length ?? 0);
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    public function bindValue($param, $value, $type = ParameterType::STRING): bool
    {
        try {
            return $this->pdoStatement->bindValue($param, $value, $this->convertParamType($type));
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    public function execute($params = null): Result
    {
        try {
            $this->pdoStatement->execute($params ?? []);
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }

        return new DbalResult($this->pdoStatement);
    }

    private function convertParamType(int $type): int
    {
        return match ($type) {
            ParameterType::NULL => PDO::PARAM_NULL,
            ParameterType::INTEGER => PDO::PARAM_INT,
            ParameterType::STRING,
            ParameterType::ASCII => PDO::PARAM_STR,
            ParameterType::BINARY,
            ParameterType::LARGE_OBJECT => PDO::PARAM_LOB,
            ParameterType::BOOLEAN => PDO::PARAM_BOOL,
            default => PDO::PARAM_STR,
        };
    }
}
