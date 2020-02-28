<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Traits\DoctrinePaginatorTrait;

final class DoctrineDbalLengthAwarePaginator extends AbstractTransformableLengthAwarePaginator
{
    use DoctrinePaginatorTrait;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $conn;

    /**
     * DoctrineDbalLengthAwarePaginator constructor.
     *
     * @param \Doctrine\DBAL\Connection $conn
     * @param string $from
     * @param \EonX\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     * @param null|string $fromAlias
     */
    public function __construct(
        Connection $conn,
        string $from,
        StartSizeDataInterface $startSizeData,
        ?string $fromAlias = null
    ) {
        parent::__construct($startSizeData);

        $this->conn = $conn;
        $this->from = $from;
        $this->fromAlias = $fromAlias;
    }

    /**
     * Class using trait must create query builder.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function doCreateQueryBuilder()
    {
        return $this->conn->createQueryBuilder();
    }

    /**
     * Class using trait must get result from given query builder.
     *
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     *
     * @return mixed[]
     */
    protected function doGetResult(QueryBuilder $queryBuilder): array
    {
        return $this->conn->fetchAll($queryBuilder->getSQL(), $queryBuilder->getParameters());
    }

    /**
     * Class using trait must define how to get total items.
     *
     * @param \Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     * @param string $countAlias
     *
     * @return int
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function doGetTotalItems(QueryBuilder $queryBuilder, string $countAlias): int
    {
        $result = $this->conn->fetchAssoc($queryBuilder->getSQL(), $queryBuilder->getParameters());

        return (int)($result[$countAlias] ?? 0);
    }
}
