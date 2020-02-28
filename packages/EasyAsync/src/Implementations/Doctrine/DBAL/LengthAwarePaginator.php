<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Implementations\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Paginators\AbstractLengthAwarePaginator;

final class LengthAwarePaginator extends AbstractLengthAwarePaginator
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $conn;

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
     * @var null|callable
     */
    private $getItemsCriteria;

    /**
     * @var mixed[]
     */
    private $items;

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
     * @param \Doctrine\DBAL\Connection $conn
     * @param \EonX\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     * @param string $from
     */
    public function __construct(Connection $conn, StartSizeDataInterface $startSizeData, string $from)
    {
        $this->conn = $conn;
        $this->from = $from;

        parent::__construct($startSizeData);
    }

    /**
     * Get items.
     *
     * @return mixed[]
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getItems(): array
    {
        if ($this->items !== null) {
            return $this->items;
        }

        $queryBuilder = $this->createQueryBuilder()->select($this->select ?? '*');

        if ($this->getItemsCriteria !== null) {
            \call_user_func($this->getItemsCriteria, $queryBuilder);
        }

        $queryBuilder
            ->setFirstResult(($this->start - 1) * $this->size)
            ->setMaxResults($this->size);

        $result = $this->conn->fetchAll($queryBuilder->getSQL(), $queryBuilder->getParameters());

        if ($this->transformer !== null) {
            $result = \array_map($this->transformer, $result);
        }

        return $this->items = $result;
    }

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

        $countAlias = '_count';
        $queryBuilder = $this->createQueryBuilder()->select(\sprintf('COUNT(1) as %s', $countAlias));
        $result = $this->conn->fetchAssoc($queryBuilder->getSQL(), $queryBuilder->getParameters());

        return $this->count = (int)($result[$countAlias] ?? 0);
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
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function createQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->conn->createQueryBuilder()->from($this->from);

        if ($this->criteria !== null) {
            \call_user_func($this->criteria, $queryBuilder);
        }

        return $queryBuilder;
    }
}
