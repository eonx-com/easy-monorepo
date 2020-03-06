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

    protected function doCreateQueryBuilder(): QueryBuilder
    {
        return $this->conn->createQueryBuilder();
    }

    /**
     * @return mixed[]
     */
    protected function doGetResult(QueryBuilder $queryBuilder): array
    {
        return $this->conn->fetchAll($queryBuilder->getSQL(), $queryBuilder->getParameters());
    }

    protected function doGetTotalItems(QueryBuilder $queryBuilder, string $countAlias): int
    {
        $result = (array)$this->conn->fetchAssoc($queryBuilder->getSQL(), $queryBuilder->getParameters());

        return (int)($result[$countAlias] ?? 0);
    }
}
