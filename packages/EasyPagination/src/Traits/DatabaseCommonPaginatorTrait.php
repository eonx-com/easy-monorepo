<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Traits;

use Doctrine\DBAL\Query\QueryBuilder as DbalQueryBuilder;
use Doctrine\ORM\QueryBuilder as OrmQueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as IlluminateQueryBuilder;

trait DatabaseCommonPaginatorTrait
{
    /**
     * @var mixed[]
     */
    private array $commonCriteria = [];

    /**
     * @var mixed[]
     */
    private array $filterCriteria = [];

    /**
     * @var mixed[]
     */
    private array $getItemsCriteria = [];

    private bool $hasJoinsInQuery = false;

    private ?string $primaryKeyIndex = 'id';

    private mixed $select = null;

    public function hasJoinsInQuery(?bool $hasJoinsInQuery = null): self
    {
        $this->hasJoinsInQuery = $hasJoinsInQuery ?? true;

        return $this;
    }

    public function addCommonCriteria(callable $commonCriteria, ?string $name = null): self
    {
        $this->commonCriteria = $this->doAddCriteria($this->commonCriteria, $commonCriteria, $name);

        return $this;
    }

    public function addFilterCriteria(callable $filterCriteria, ?string $name = null): self
    {
        $this->filterCriteria = $this->doAddCriteria($this->filterCriteria, $filterCriteria, $name);

        return $this;
    }

    public function addGetItemsCriteria(callable $getItemsCriteria, ?string $name = null): self
    {
        $this->getItemsCriteria = $this->doAddCriteria($this->getItemsCriteria, $getItemsCriteria, $name);

        return $this;
    }

    public function removeCommonCriteria(string $name): self
    {
        $this->commonCriteria = $this->doRemoveCriteriaByName($this->commonCriteria, $name);

        return $this;
    }

    public function removeFilterCriteria(string $name): self
    {
        $this->filterCriteria = $this->doRemoveCriteriaByName($this->filterCriteria, $name);

        return $this;
    }

    public function removeGetItemsCriteria(string $name): self
    {
        $this->getItemsCriteria = $this->doRemoveCriteriaByName($this->getItemsCriteria, $name);

        return $this;
    }

    public function setCommonCriteria(?callable $commonCriteria = null, ?string $name = null): self
    {
        $this->commonCriteria = $this->doSetCriteria($commonCriteria, $name);

        return $this;
    }

    public function setFilterCriteria(?callable $filterCriteria = null, ?string $name = null): self
    {
        $this->filterCriteria = $this->doSetCriteria($filterCriteria, $name);

        return $this;
    }

    public function setGetItemsCriteria(?callable $getItemsCriteria = null, ?string $name = null): self
    {
        $this->getItemsCriteria = $this->doSetCriteria($getItemsCriteria, $name);

        return $this;
    }

    public function setPrimaryKeyIndex(?string $primaryKeyIndex = null): self
    {
        $this->primaryKeyIndex = $primaryKeyIndex;

        return $this;
    }

    public function setSelect(mixed $select): self
    {
        $this->select = $select;

        return $this;
    }

    private function applyCommonCriteria(
        OrmQueryBuilder|DbalQueryBuilder|EloquentBuilder|IlluminateQueryBuilder $queryBuilder,
    ): void {
        $this->doApplyCriteria($this->commonCriteria, $queryBuilder);
    }

    private function applyFilterCriteria(
        OrmQueryBuilder|DbalQueryBuilder|EloquentBuilder|IlluminateQueryBuilder $queryBuilder,
    ): void {
        $this->doApplyCriteria($this->filterCriteria, $queryBuilder);
    }

    private function applyGetItemsCriteria(
        OrmQueryBuilder|DbalQueryBuilder|EloquentBuilder|IlluminateQueryBuilder $queryBuilder,
    ): void {
        $this->doApplyCriteria($this->getItemsCriteria, $queryBuilder);
    }

    /**
     * @param mixed[] $originalCriteria
     *
     * @return mixed[]
     */
    private function doAddCriteria(array $originalCriteria, callable $criteria, ?string $name = null): array
    {
        $originalCriteria[] = [$criteria, $name];

        return $originalCriteria;
    }

    /**
     * @param mixed[] $criteria
     */
    private function doApplyCriteria(
        array $criteria,
        OrmQueryBuilder|DbalQueryBuilder|EloquentBuilder|IlluminateQueryBuilder $queryBuilder,
    ): void {
        foreach ($criteria as $criterion) {
            \call_user_func($criterion[0], $queryBuilder);
        }
    }

    /**
     * @param mixed[] $criteria
     *
     * @return mixed[]
     */
    private function doRemoveCriteriaByName(array $criteria, string $name): array
    {
        return \array_filter($criteria, static function (array $current) use ($name): bool {
            return $current[1] !== $name;
        });
    }

    /**
     * @return mixed[]
     */
    private function doSetCriteria(?callable $criteria = null, ?string $name = null): array
    {
        return $criteria !== null ? [[$criteria, $name]] : [];
    }

    private function getPrimaryKeyIndexWithDefault(): string
    {
        return $this->primaryKeyIndex ?? 'id';
    }
}
