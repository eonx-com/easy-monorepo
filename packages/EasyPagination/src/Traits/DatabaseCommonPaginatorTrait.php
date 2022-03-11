<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Traits;

trait DatabaseCommonPaginatorTrait
{
    /**
     * @var null|callable
     */
    private $commonCriteria;

    /**
     * @var null|callable
     */
    private $filterCriteria;

    /**
     * @var null|callable
     */
    private $getItemsCriteria;

    /**
     * @var bool
     */
    private $hasJoinsInQuery = false;

    /**
     * @var string
     */
    private $primaryKeyIndex = 'id';

    /**
     * @var null|mixed
     */
    private $select;

    public function hasJoinsInQuery(?bool $hasJoinsInQuery = null): self
    {
        $this->hasJoinsInQuery = $hasJoinsInQuery ?? true;

        return $this;
    }

    public function setCommonCriteria(?callable $commonCriteria = null): self
    {
        $this->commonCriteria = $commonCriteria;

        return $this;
    }

    public function setFilterCriteria(?callable $filterCriteria = null): self
    {
        $this->filterCriteria = $filterCriteria;

        return $this;
    }

    public function setGetItemsCriteria(?callable $getItemsCriteria = null): self
    {
        $this->getItemsCriteria = $getItemsCriteria;

        return $this;
    }

    public function setPrimaryKeyIndex(string $primaryKeyIndex): self
    {
        $this->primaryKeyIndex = $primaryKeyIndex;

        return $this;
    }

    /**
     * @param mixed $select
     */
    public function setSelect($select): self
    {
        $this->select = $select;

        return $this;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $queryBuilder
     */
    private function applyCommonCriteria($queryBuilder): void
    {
        if ($this->commonCriteria !== null) {
            \call_user_func($this->commonCriteria, $queryBuilder);
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $queryBuilder
     */
    private function applyFilterCriteria($queryBuilder): void
    {
        if ($this->filterCriteria !== null) {
            \call_user_func($this->filterCriteria, $queryBuilder);
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $queryBuilder
     */
    private function applyGetItemsCriteria($queryBuilder): void
    {
        if ($this->getItemsCriteria !== null) {
            \call_user_func($this->getItemsCriteria, $queryBuilder);
        }
    }
}
