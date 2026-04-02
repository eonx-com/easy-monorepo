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

final readonly class DbalStatement implements Statement
{
    public function __construct(
        private PDOStatementProxy $pdoStatement,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function bindValue(int|string $param, mixed $value, ParameterType $type): void
    {
        try {
            $this->pdoStatement->bindValue($param, $value, $this->convertParamType($type));
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): Result
    {
        try {
            $this->pdoStatement->execute();
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }

        return new DbalResult($this->pdoStatement);
    }

    private function convertParamType(ParameterType $type): int
    {
        return match ($type) {
            ParameterType::NULL => PDO::PARAM_NULL,
            ParameterType::INTEGER => PDO::PARAM_INT,
            ParameterType::STRING,
            ParameterType::ASCII => PDO::PARAM_STR,
            ParameterType::BINARY,
            ParameterType::LARGE_OBJECT => PDO::PARAM_LOB,
            ParameterType::BOOLEAN => PDO::PARAM_BOOL,
        };
    }
}
