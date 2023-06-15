<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Traits;

use Illuminate\Database\Eloquent\Builder;

trait EloquentPaginatorTrait
{
    use DatabaseCommonPaginatorTrait;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $model;

    /**
     * @var int
     */
    private $totalItems;

    /**
     * @return mixed[]
     */
    protected function doGetItems(): array
    {
        return $this->fetchItems();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $queryBuilder
     */
    private function applyPagination($queryBuilder): void
    {
        $queryBuilder->forPage($this->getCurrentPage(), $this->getItemsPerPage());
    }

    private function createQueryBuilder(): Builder
    {
        return $this->model->newQuery();
    }

    private function doGetTotalItems(): int
    {
        if ($this->totalItems !== null) {
            return $this->totalItems;
        }

        $queryBuilder = $this->createQueryBuilder();

        $this->applyCommonCriteria($queryBuilder);
        $this->applyFilterCriteria($queryBuilder);

        return $queryBuilder->count();
    }

    /**
     * @return mixed[]
     */
    private function fetchItems(): array
    {
        $queryBuilder = $this->createQueryBuilder();

        if ($this->select !== null) {
            $queryBuilder->select($this->select);
        }

        // Get items criteria are applied regardless of fetching method
        $this->applyCommonCriteria($queryBuilder);
        $this->applyGetItemsCriteria($queryBuilder);

        return $this->hasJoinsInQuery === false
            ? $this->fetchItemsUsingQuery($queryBuilder)
            : $this->fetchItemsUsingPrimaryKeys($queryBuilder);
    }

    /**
     * @return mixed[]
     */
    private function fetchItemsUsingPrimaryKeys(Builder $queryBuilder): array
    {
        $primaryKeyQueryBuilder = $this->createQueryBuilder();

        // Apply pagination and criteria to get primary keys only for current page, and criteria
        $this->applyCommonCriteria($primaryKeyQueryBuilder);
        $this->applyFilterCriteria($primaryKeyQueryBuilder);
        $this->applyGetItemsCriteria($primaryKeyQueryBuilder);
        $this->applyPagination($primaryKeyQueryBuilder);

        $primaryKeyIndex = $this->getPrimaryKeyIndexWithDefault();
        // Prefix primaryKey with table to avoid ambiguous conflicts
        $prefixedPrimaryKey = \sprintf('%s.%s', $this->model->getTable(), $primaryKeyIndex);
        // Override select to fetch only primary key
        $primaryKeyQueryBuilder->select($prefixedPrimaryKey);

        /** @var \Illuminate\Database\Eloquent\Collection<array-key, \Illuminate\Database\Eloquent\Model> $result */
        $result = $primaryKeyQueryBuilder->get();

        $primaryKeys = $result->pluck($primaryKeyIndex)
            ->all();

        // If no primary keys, no items for current pagination
        if (\count($primaryKeys) === 0) {
            return [];
        }

        // Filter records on their primary keys
        $queryBuilder->whereIn($prefixedPrimaryKey, $primaryKeys);

        return $this->fetchResults($queryBuilder);
    }

    /**
     * @return mixed[]
     */
    private function fetchItemsUsingQuery(Builder $queryBuilder): array
    {
        $this->applyFilterCriteria($queryBuilder);
        $this->applyPagination($queryBuilder);

        return $this->fetchResults($queryBuilder);
    }

    /**
     * @return mixed[]
     */
    private function fetchResults(Builder $queryBuilder): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<array-key, \Illuminate\Database\Eloquent\Model> $result */
        $result = $queryBuilder->get();

        return \array_values($result->getDictionary());
    }
}
