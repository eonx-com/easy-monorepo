<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

trait DoctrineOrmPaginatorTrait
{
    use DoctrineCommonPaginatorTrait;

    /**
     * @var class-string
     */
    private string $from;

    private ?string $indexBy = null;

    private EntityManagerInterface $manager;

    private function createQueryBuilder(): QueryBuilder
    {
        return $this->manager->createQueryBuilder()
            ->from($this->from, $this->fromAlias, $this->indexBy);
    }

    private function fetchResults(QueryBuilder $queryBuilder): array
    {
        /** @var array $result */
        $result = $queryBuilder->getQuery()
            ->getResult();

        return $result;
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
