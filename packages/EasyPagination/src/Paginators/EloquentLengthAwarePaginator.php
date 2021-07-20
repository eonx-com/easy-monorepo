<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated since 3.2, will be removed in 4.0. Will be replace by new implementation using Pagination.
 */
final class EloquentLengthAwarePaginator extends AbstractTransformableLengthAwarePaginator
{
    /**
     * @var int
     */
    private $count;

    /**
     * @var null|callable
     */
    private $criteria;

    /**
     * @var null|callable
     */
    private $getItemsCriteria;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $model;

    /**
     * @var null|string[]
     */
    private $select;

    public function __construct(Model $model, StartSizeDataInterface $startSizeData)
    {
        $this->model = $model;

        parent::__construct($startSizeData);
    }

    public function getTotalItems(): int
    {
        if ($this->count !== null) {
            return $this->count;
        }

        return $this->count = $this->createQueryBuilder()
            ->count(\implode(',', $this->getSelect()));
    }

    public function setCriteria(?callable $criteria = null): self
    {
        $this->criteria = $criteria;

        return $this;
    }

    public function setGetItemsCriteria(?callable $criteria = null): self
    {
        $this->getItemsCriteria = $criteria;

        return $this;
    }

    /**
     * @param string[] $select
     */
    public function setSelect(array $select): self
    {
        $this->select = $select;

        return $this;
    }

    /**
     * @return mixed[]
     */
    protected function doGetItems(): array
    {
        $queryBuilder = $this->createQueryBuilder();

        if ($this->getItemsCriteria !== null) {
            \call_user_func($this->getItemsCriteria, $queryBuilder);
        }

        $queryBuilder->forPage($this->paginationData->getStart(), $this->paginationData->getSize());

        return \iterator_to_array($queryBuilder->get($this->getSelect())->getIterator());
    }

    /**
     * @return string[]
     */
    protected function getSelect(): array
    {
        return $this->select ?? ['*'];
    }

    private function createQueryBuilder(): Builder
    {
        $queryBuilder = $this->model->newQuery();

        if ($this->criteria !== null) {
            \call_user_func($this->criteria, $queryBuilder);
        }

        return $queryBuilder;
    }
}
