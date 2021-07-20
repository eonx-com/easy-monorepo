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

        $this->applyCriteria($queryBuilder);

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
        // Prefix primaryKey with table to avoid ambiguous conflicts
        $prefixedPrimaryKey = \sprintf('%s.%s', $this->model->getTable(), $this->primaryKeyIndex);

        $newQueryBuilder = $this->createQueryBuilder()
            ->select($prefixedPrimaryKey);

        // Apply pagination and criteria to get primary keys only for current page, and criteria
        $this->applyCriteria($newQueryBuilder);
        $this->applyPagination($newQueryBuilder);

        $primaryKeys = $newQueryBuilder->get()->pluck($this->primaryKeyIndex)->all();

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
        $this->applyCriteria($queryBuilder);
        $this->applyPagination($queryBuilder);

        return $this->fetchResults($queryBuilder);
    }

    /**
     * @return mixed[]
     */
    private function fetchResults(Builder $queryBuilder): array
    {
        return \array_values($queryBuilder->get()->getDictionary());
    }
}
