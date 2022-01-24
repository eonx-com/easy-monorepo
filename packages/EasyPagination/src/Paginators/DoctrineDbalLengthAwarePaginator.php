<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Version;
use EonX\EasyPagination\Exceptions\InvalidPrimaryKeyIndexException;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Traits\DoctrinePaginatorTrait;

/**
 * @deprecated since 3.2, will be removed in 4.0. Will be replace by new implementation using Pagination.
 */
final class DoctrineDbalLengthAwarePaginator extends AbstractTransformableLengthAwarePaginator
{
    use DoctrinePaginatorTrait;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $conn;

    /**
     * @var string
     */
    private $primaryKeyIndex;

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
     * @param \Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     *
     * @return mixed[]
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function doGetResult($queryBuilder): array
    {
        return $this->conn->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());
    }

    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function doGetTotalItems($queryBuilder, string $countAlias): int
    {
        // @TODO remove method_exists check after update DBAL version to >=2.11
        if (\method_exists($this->conn, 'fetchAssociative')) {
            $result = (array)$this->conn->fetchAssociative(
                $queryBuilder->getSQL(),
                $queryBuilder->getParameters(),
                $queryBuilder->getParameterTypes()
            );
        } else {
            $result = (array)$this->conn->fetchAssoc($queryBuilder->getSQL(), $queryBuilder->getParameters());
        }

        return (int)($result[$countAlias] ?? 0);
    }

    protected function getPrimaryKeyIndex(): string
    {
        if ($this->primaryKeyIndex !== null) {
            return $this->primaryKeyIndex;
        }

        $indexes = $this->conn->getSchemaManager()
            ->listTableIndexes($this->from);

        foreach ($indexes as $index) {
            if ($index->isPrimary()) {
                $columns = $index->getColumns();

                if (\count($columns) !== 1) {
                    throw new InvalidPrimaryKeyIndexException(\sprintf(
                        'Only PrimaryKey index with 1 column supported, %d given for table "%s". ["%s"]',
                        \count($columns),
                        $this->from,
                        \implode('", "', $columns)
                    ));
                }

                return $this->primaryKeyIndex = (string)\reset($columns);
            }
        }

        throw new InvalidPrimaryKeyIndexException(\sprintf(
            'No PrimaryKey index identified for table "%s"',
            $this->from
        ));
    }
}
