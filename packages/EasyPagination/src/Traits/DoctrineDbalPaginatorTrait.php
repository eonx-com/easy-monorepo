<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Traits;

use Doctrine\DBAL\Query\QueryBuilder;

trait DoctrineDbalPaginatorTrait
{
    use DoctrineCommonPaginatorTrait;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $conn;

    private function createQueryBuilder(): QueryBuilder
    {
        return $this->conn->createQueryBuilder()
            ->from($this->from, $this->fromAlias);
    }

    /**
     * @return mixed[]
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchResults(QueryBuilder $queryBuilder): array
    {
        return $this->conn->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());
    }

    /**
     * @return mixed
     */
    private function resolveSelect()
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
