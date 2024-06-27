<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

trait DoctrineOrmPaginatorTrait
{
    use DoctrineCommonPaginatorTrait;

    private ?string $indexBy = null;

    private EntityManagerInterface $manager;

    private function createQueryBuilder(): QueryBuilder
    {
        return $this->manager->createQueryBuilder()
            ->from($this->from, $this->fromAlias, $this->indexBy);
    }

    private function fetchResults(QueryBuilder $queryBuilder): array
    {
        return $queryBuilder->getQuery()
            ->getResult();
    }

    private function getConnection(): Connection
    {
        return $this->manager->getConnection();
    }

    private function resolveSelect(): mixed
    {
        // If select is "*" simply return fromAlias so orm selects everything
        if ($this->select !== null && $this->select !== '*') {
            return $this->select;
        }

        return $this->fromAlias;
    }
}
