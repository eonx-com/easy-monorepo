<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Traits;

use Doctrine\ORM\QueryBuilder;

trait DoctrineOrmPaginatorTrait
{
    use DoctrineCommonPaginatorTrait;

    /**
     * @var null|string
     */
    private $indexBy;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $manager;

    private function createQueryBuilder(): QueryBuilder
    {
        return $this->manager->createQueryBuilder()
            ->from($this->from, $this->fromAlias, $this->indexBy);
    }

    /**
     * @return mixed[]
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchResults(QueryBuilder $queryBuilder): array
    {
        return $queryBuilder->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     */
    private function resolveSelect()
    {
        // If select is "*" simply return fromAlias so orm selects everything
        if ($this->select !== null && $this->select !== '*') {
            return $this->select;
        }

        return $this->fromAlias;
    }
}
