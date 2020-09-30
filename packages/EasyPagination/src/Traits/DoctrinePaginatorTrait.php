<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Traits;

trait DoctrinePaginatorTrait
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
     * @var string
     */
    private $from;

    /**
     * @var null|string
     */
    private $fromAlias;

    /**
     * @var null|callable
     */
    private $getItemsCriteria;

    /**
     * @var bool
     */
    private $hasJoinsInQuery = false;

    /**
     * @var null|mixed
     */
    private $select;

    public function getTotalItems(): int
    {
        if ($this->count !== null) {
            return $this->count;
        }

        $fromAlias = $this->getFromAlias(true);
        $countAlias = \sprintf('_count_%s', $fromAlias);
        $sql = \sprintf('COUNT(DISTINCT %s) as %s', $fromAlias, $countAlias);

        $queryBuilder = $this->createQueryBuilder()->select($sql);

        return $this->count = $this->doGetTotalItems($queryBuilder, $countAlias);
    }

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

    /**
     * @param null|mixed $select
     */
    public function setSelect($select = null): self
    {
        $this->select = $select;

        return $this;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder
     */
    abstract protected function doCreateQueryBuilder();

    /**
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     *
     * @return mixed[]
     */
    abstract protected function doGetResult($queryBuilder): array;

    /**
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     */
    abstract protected function doGetTotalItems($queryBuilder, string $countAlias): int;

    abstract protected function getPrimaryKeyIndex(): string;

    /**
     * @return mixed[]
     */
    protected function doGetItems(): array
    {
        $queryBuilder = $this->createQueryBuilder()->select($this->getSelect());

        if ($this->getItemsCriteria !== null) {
            \call_user_func($this->getItemsCriteria, $queryBuilder);
        }

        if ($this->hasJoinsInQuery === false) {
            $this->applyPagination($queryBuilder);

            return $this->doGetResult($queryBuilder);
        }

        return $this->doGetItemsUsingPrimaryKeys($queryBuilder);
    }

    protected function getFromAlias(?bool $forCount = null): string
    {
        if ($this->fromAlias !== null) {
            return $this->fromAlias;
        }

        $forCount = $forCount ?? false;

        return $forCount ? '1' : $this->from;
    }

    /**
     * @return null|mixed|string
     */
    protected function getSelect()
    {
        return $this->select ?? $this->fromAlias ?? '*';
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     */
    private function applyPagination($queryBuilder): void
    {
        $queryBuilder
            ->setFirstResult(($this->paginationData->getStart() - 1) * $this->paginationData->getSize())
            ->setMaxResults($this->paginationData->getSize());
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder
     */
    private function createQueryBuilder()
    {
        $queryBuilder = $this->doCreateQueryBuilder()->from($this->from, $this->fromAlias);

        if ($this->criteria !== null) {
            \call_user_func($this->criteria, $queryBuilder);
        }

        return $queryBuilder;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     *
     * @return mixed[]
     */
    private function doGetItemsUsingPrimaryKeys($queryBuilder): array
    {
        $primaryKeyIndex = $this->getPrimaryKeyIndex();
        $select = \sprintf('%s.%s', $this->getFromAlias(), $primaryKeyIndex);
        $newQueryBuilder = $this->createQueryBuilder()->select($select);

        // Apply pagination to get primary keys only for current page
        $this->applyPagination($newQueryBuilder);

        $primaryKeys = \array_map(static function (array $row) use ($primaryKeyIndex): string {
            return $row[$primaryKeyIndex];
        }, $newQueryBuilder->getQuery()->getResult());

        // If no primary keys, no items for current pagination
        if (\count($primaryKeys) === 0) {
            return [];
        }

        // Filter records on their primary keys
        $queryBuilder->andWhere($queryBuilder->expr()->in($select, $primaryKeys));

        return $this->doGetResult($queryBuilder);
    }
}
