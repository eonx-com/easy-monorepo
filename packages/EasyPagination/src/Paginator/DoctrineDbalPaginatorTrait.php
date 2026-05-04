<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

use BackedEnum;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

trait DoctrineDbalPaginatorTrait
{
    use DoctrineCommonPaginatorTrait;

    private Connection $connection;

    private string $from;

    private function createQueryBuilder(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->from($this->from, $this->fromAlias);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchResults(QueryBuilder $queryBuilder): array
    {
        $params = $queryBuilder->getParameters();
        $paramTypes = $queryBuilder->getParameterTypes();

        foreach ($params as $key => $param) {
            if ($param instanceof BackedEnum) {
                $params[$key] = $param->value;
                $paramTypes[$key] = \is_int($param->value) ? ParameterType::INTEGER : ParameterType::STRING;
            }
        }

        return $this->connection->fetchAllAssociative($queryBuilder->getSQL(), $params, $paramTypes);
    }

    private function getConnection(): Connection
    {
        return $this->connection;
    }

    private function resolveSelect(): mixed
    {
        if ($this->select !== null) {
            return $this->select;
        }

        if ($this->fromAlias !== null) {
            return \sprintf('%s.*', $this->fromAlias);
        }

        return '*';
    }
}
