<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

use Doctrine\DBAL\Connection;
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
        return $this->connection->fetchAllAssociative(
            $queryBuilder->getSQL(),
            $queryBuilder->getParameters(),
            $queryBuilder->getParameterTypes()
        );
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
