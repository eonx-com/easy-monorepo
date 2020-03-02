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
     * @var null|mixed
     */
    private $select;

    /**
     * Get total items.
     *
     * @return int
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getTotalItems(): int
    {
        if ($this->count !== null) {
            return $this->count;
        }

        $countAlias = \sprintf('_count%s', $this->fromAlias ? \sprintf('_%s', $this->fromAlias) : '');
        $queryBuilder = $this->createQueryBuilder()->select(\sprintf('COUNT(1) as %s', $countAlias));

        return $this->count = $this->doGetTotalItems($queryBuilder, $countAlias);
    }

    /**
     * Set criteria.
     *
     * @param null|callable $criteria
     *
     * @return $this
     */
    public function setCriteria(?callable $criteria = null): self
    {
        $this->criteria = $criteria;

        return $this;
    }

    /**
     * Set criteria for query to fetch items.
     *
     * @param null|callable $getItemsCriteria
     *
     * @return $this
     */
    public function setGetItemsCriteria(?callable $getItemsCriteria = null): self
    {
        $this->getItemsCriteria = $getItemsCriteria;

        return $this;
    }

    /**
     * Set select to fetch items.
     *
     * @param null|mixed $select
     *
     * @return $this
     */
    public function setSelect($select = null): self
    {
        $this->select = $select;

        return $this;
    }

    /**
     * Class using trait must create query builder.
     *
     * @return \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder
     */
    abstract protected function doCreateQueryBuilder();

    /**
     * Class using trait must get result from given query builder.
     *
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     *
     * @return mixed[]
     */
    abstract protected function doGetResult($queryBuilder): array;

    /**
     * Class using trait must define how to get total items.
     *
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     * @param string $countAlias
     *
     * @return int
     */
    abstract protected function doGetTotalItems($queryBuilder, string $countAlias): int;

    /**
     * Children classes must implement getItems themselves.
     *
     * @return mixed[]
     */
    protected function doGetItems(): array
    {
        $queryBuilder = $this->createQueryBuilder()->select($this->getSelect());

        if ($this->getItemsCriteria !== null) {
            \call_user_func($this->getItemsCriteria, $queryBuilder);
        }

        $queryBuilder
            ->setFirstResult(($this->start - 1) * $this->size)
            ->setMaxResults($this->size);

        return $this->doGetResult($queryBuilder);
    }

    /**
     * Get select.
     *
     * @return null|mixed|string
     */
    protected function getSelect()
    {
        return $this->select ?? $this->fromAlias ?? '*';
    }

    /**
     * Create query builder and apply criteria.
     *
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
}
