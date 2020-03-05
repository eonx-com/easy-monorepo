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

    public function getTotalItems(): int
    {
        if ($this->count !== null) {
            return $this->count;
        }

        $countAlias = \sprintf('_count%s', $this->fromAlias ? \sprintf('_%s', $this->fromAlias) : '');
        $queryBuilder = $this->createQueryBuilder()->select(\sprintf('COUNT(1) as %s', $countAlias));

        return $this->count = $this->doGetTotalItems($queryBuilder, $countAlias);
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

    /**
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
     * @return null|mixed|string
     */
    protected function getSelect()
    {
        return $this->select ?? $this->fromAlias ?? '*';
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
}
