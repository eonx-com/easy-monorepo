<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Implementations\Doctrine\ORM\Paginators;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Paginators\AbstractLengthAwarePaginator;

final class LengthAwarePaginator extends AbstractLengthAwarePaginator
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
     * @var null|string
     */
    private $indexBy;

    /**
     * @var mixed[]
     */
    private $items;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $manager;

    /**
     * @var null|mixed
     */
    private $select;

    /**
     * @var null|callable
     */
    private $transformer;

    /**
     * LengthAwarePaginator constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $manager
     * @param \EonX\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     * @param string $from
     * @param string $fromAlias
     * @param null|string $indexBy
     */
    public function __construct(
        EntityManagerInterface $manager,
        StartSizeDataInterface $startSizeData,
        string $from,
        string $fromAlias,
        ?string $indexBy = null
    ) {
        $this->from = $from;
        $this->fromAlias = $fromAlias;
        $this->indexBy = $indexBy;
        $this->manager = $manager;

        parent::__construct($startSizeData);
    }

    /**
     * Get items.
     *
     * @return mixed[]
     */
    public function getItems(): array
    {
        if ($this->items !== null) {
            return $this->items;
        }

        $queryBuilder = $this->createQueryBuilder()->select($this->select ?? $this->fromAlias);

        if ($this->getItemsCriteria !== null) {
            \call_user_func($this->getItemsCriteria, $queryBuilder);
        }

        $result = $queryBuilder
            ->setFirstResult(($this->start - 1) * $this->size)
            ->setMaxResults($this->size)
            ->getQuery()
            ->getResult();

        if ($this->transformer !== null) {
            $result = \array_map($this->transformer, $result);
        }

        return $this->items = $result;
    }

    /**
     * Get total items.
     *
     * @return int
     */
    public function getTotalItems(): int
    {
        if ($this->count !== null) {
            return $this->count;
        }

        $countAlias = \sprintf('_count_%s', $this->fromAlias);
        $queryBuilder = $this->createQueryBuilder()->select(\sprintf('COUNT(1) as %s', $countAlias));

        return $this->count = (int)($queryBuilder->getQuery()->getResult()[0][$countAlias] ?? 0);
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
     * Set transformer to apply to each item fetched.
     *
     * @param null|callable $transformer
     *
     * @return $this
     */
    public function setTransformer(?callable $transformer = null): self
    {
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * Create query builder and apply criteria.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function createQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->manager->createQueryBuilder()->from($this->from, $this->fromAlias, $this->indexBy);

        if ($this->criteria !== null) {
            \call_user_func($this->criteria, $queryBuilder);
        }

        return $queryBuilder;
    }
}
