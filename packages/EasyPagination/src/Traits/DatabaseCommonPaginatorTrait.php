<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Traits;

trait DatabaseCommonPaginatorTrait
{
    /**
     * @var null|callable
     */
    private $criteria;

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

    public function setCriteria(?callable $criteria = null): self
    {
        $this->criteria = $criteria;

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
    private function applyCriteria($queryBuilder): void
    {
        if ($this->criteria !== null) {
            \call_user_func($this->criteria, $queryBuilder);
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
