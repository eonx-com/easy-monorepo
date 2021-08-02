<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Traits;

trait DoctrineCommonPaginatorTrait
{
    use DatabaseCommonPaginatorTrait;

    /**
     * @var string
     */
    private $from;

    /**
     * @var null|string
     */
    private $fromAlias;

    /**
     * @var int
     */
    private $totalItems;

    /**
     * @return mixed[]
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function doGetItems(): array
    {
        return $this->fetchItems();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     */
    private function applyPagination($queryBuilder): void
    {
        $queryBuilder
            ->setFirstResult(($this->getCurrentPage() - 1) * $this->getItemsPerPage())
            ->setMaxResults($this->getItemsPerPage());
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function doGetTotalItems(): int
    {
        if ($this->totalItems !== null) {
            return $this->totalItems;
        }

        $countAlias = \sprintf('_count_%s', $this->fromAlias ?? '1');
        $countSelect = '1';

        if ($this->fromAlias !== null && $this->primaryKeyIndex !== null) {
            $countSelect = \sprintf('DISTINCT %s.%s', $this->fromAlias, $this->primaryKeyIndex);
        }

        $queryBuilder = $this->createQueryBuilder()
            ->select(\sprintf('COUNT(%s) as %s', $countSelect, $countAlias));

        $this->applyCommonCriteria($queryBuilder);
        $this->applyFilterCriteria($queryBuilder);

        $results = $this->fetchResults($queryBuilder);

        return $this->totalItems = (int)($results[0][$countAlias] ?? 0);
    }

    /**
     * @return mixed[]
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchItems(): array
    {
        $queryBuilder = $this->createQueryBuilder()
            ->select($this->resolveSelect());

        // Common and GetItems criteria are applied regardless of fetching method
        $this->applyCommonCriteria($queryBuilder);
        $this->applyGetItemsCriteria($queryBuilder);

        return $this->hasJoinsInQuery === false
            ? $this->fetchItemsUsingQuery($queryBuilder)
            : $this->fetchItemsUsingPrimaryKeys($queryBuilder);
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     *
     * @return mixed[]
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchItemsUsingPrimaryKeys($queryBuilder): array
    {
        $fetchPrimaryKeysQueryBuilder = $this->createQueryBuilder();

        // Fetch PrimaryKey for current page, and all criteria
        $this->applyCommonCriteria($fetchPrimaryKeysQueryBuilder);
        $this->applyFilterCriteria($fetchPrimaryKeysQueryBuilder);
        $this->applyGetItemsCriteria($fetchPrimaryKeysQueryBuilder);
        $this->applyPagination($fetchPrimaryKeysQueryBuilder);

        // Override select to fetch only primary key
        $primaryKeyIndex = $this->primaryKeyIndex;
        $select = \sprintf('%s.%s', $this->fromAlias ?? $this->from, $primaryKeyIndex);
        $fetchPrimaryKeysQueryBuilder->select($select);

        $primaryKeysMap = static function (array $row) use ($primaryKeyIndex) {
            return $row[$primaryKeyIndex];
        };

        /** @var string[] $primaryKeys */
        $primaryKeys = \array_map($primaryKeysMap, $this->fetchResults($fetchPrimaryKeysQueryBuilder));

        // If no primary keys, no items for current pagination
        if (\count($primaryKeys) === 0) {
            return [];
        }

        // Filter records on their primary keys
        $queryBuilder->andWhere($queryBuilder->expr()->in($select, $primaryKeys));

        return $this->fetchResults($queryBuilder);
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     *
     * @return mixed[]
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchItemsUsingQuery($queryBuilder): array
    {
        $this->applyFilterCriteria($queryBuilder);
        $this->applyPagination($queryBuilder);

        return $this->fetchResults($queryBuilder);
    }
}
