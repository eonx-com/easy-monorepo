<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Query\QueryBuilder as DbalQueryBuilder;
use Doctrine\ORM\QueryBuilder as OrmQueryBuilder;

use function Symfony\Component\String\u;

trait DoctrineCommonPaginatorTrait
{
    use DatabaseCommonPaginatorTrait;

    private ?string $fromAlias = null;

    private ?int $totalItems = null;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function doGetItems(): array
    {
        return $this->fetchItems();
    }

    private function applyPagination(OrmQueryBuilder|DbalQueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->setFirstResult(($this->getCurrentPage() - 1) * $this->getItemsPerPage())
            ->setMaxResults($this->getItemsPerPage());
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function doGetTotalItems(): int
    {
        if ($this->totalItems !== null) {
            return $this->totalItems;
        }

        $queryBuilder = $this->createQueryBuilder();

        $this->applyCommonCriteria($queryBuilder);
        $this->applyFilterCriteria($queryBuilder);

        if ($this->isLargeDatasetEnabled()) {
            $totalItems = $this->getTotalItemsForLargeDataset($queryBuilder);

            if ($totalItems !== null) {
                return $this->totalItems = $totalItems;
            }
        }

        $countAlias = \sprintf('_count_%s', $this->fromAlias ?? '1');
        $countSelect = '1';

        if ($this->fromAlias !== null && $this->primaryKeyIndex !== null) {
            $countSelect = \sprintf('DISTINCT %s.%s', $this->fromAlias, $this->primaryKeyIndex);
        }

        $queryBuilder = $queryBuilder->select(\sprintf('COUNT(%s) as %s', $countSelect, $countAlias));

        $results = $this->fetchResults($queryBuilder);

        return $this->totalItems = (int)($results[0][$countAlias] ?? 0);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchItems(): array
    {
        $queryBuilder = $this->createQueryBuilder()
            ->select($this->resolveSelect());

        // Common and GetItems criteria are applied regardless of fetching method
        $this->applyCommonCriteria($queryBuilder);
        $this->applyGetItemsCriteria($queryBuilder);

        return $this->hasJoinInQuery($queryBuilder) === false
            ? $this->fetchItemsUsingQuery($queryBuilder)
            : $this->fetchItemsUsingPrimaryKeys($queryBuilder);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchItemsUsingPrimaryKeys(OrmQueryBuilder|DbalQueryBuilder $queryBuilder): array
    {
        $fetchPrimaryKeysQueryBuilder = $this->createQueryBuilder();

        // Fetch PrimaryKey for current page, and all criteria
        $this->applyCommonCriteria($fetchPrimaryKeysQueryBuilder);
        $this->applyFilterCriteria($fetchPrimaryKeysQueryBuilder);
        $this->applyGetItemsCriteria($fetchPrimaryKeysQueryBuilder);
        $this->applyPagination($fetchPrimaryKeysQueryBuilder);

        // Override select to fetch only primary key
        $primaryKeyIndex = $this->getPrimaryKeyIndexWithDefault();
        $select = \sprintf('%s.%s', $this->fromAlias ?? $this->from, $primaryKeyIndex);
        $fetchPrimaryKeysQueryBuilder->select($select);

        $primaryKeysMap = static fn (array $row) => $row[$primaryKeyIndex];

        /** @var string[] $primaryKeys */
        $primaryKeys = \array_map($primaryKeysMap, $this->fetchResults($fetchPrimaryKeysQueryBuilder));

        // If no primary keys, no items for current pagination
        if (\count($primaryKeys) === 0) {
            return [];
        }

        // Filter records on their primary keys
        $queryBuilder->andWhere($queryBuilder->expr()->in($select, $primaryKeys));

        return $this->fetchResults($queryBuilder);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchItemsUsingQuery(OrmQueryBuilder|DbalQueryBuilder $queryBuilder): array
    {
        $this->applyFilterCriteria($queryBuilder);
        $this->applyPagination($queryBuilder);

        return $this->fetchResults($queryBuilder);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function getTotalItemsForLargeDataset(OrmQueryBuilder|DbalQueryBuilder $queryBuilder): ?int
    {
        $connection = $this->getConnection();
        $platform = $connection->getDatabasePlatform();

        if ($platform instanceof PostgreSQLPlatform === false
            && $platform instanceof SQLitePlatform === false) {
            return null;
        }

        $queryBuilder->select('1');

        $sql = null;
        $params = [];
        $paramTypes = [];

        if ($queryBuilder instanceof OrmQueryBuilder) {
            $sql = $queryBuilder->getQuery()
                ->getSQL();

            foreach ($queryBuilder->getParameters() as $param) {
                $params[] = $param->getValue();
                $paramTypes[] = $param->getType();
            }
        }

        if ($queryBuilder instanceof DbalQueryBuilder) {
            $sql = $queryBuilder->getSQL();
            $params = $queryBuilder->getParameters();
            $paramTypes = $queryBuilder->getParameterTypes();
        }

        if (\is_string($sql) === false) {
            return null;
        }

        if ($platform instanceof PostgreSQLPlatform) {
            $sql = \sprintf('EXPLAIN %s', $sql);
        }
        if ($platform instanceof SQLitePlatform) {
            $sql = \sprintf('EXPLAIN QUERY PLAN %s', $sql);
        }

        $result = $connection->executeQuery($sql, $params, $paramTypes)
            ->fetchAssociative();

        if (\is_array($result) && isset($result['QUERY PLAN'])) {
            $matches = u($result['QUERY PLAN'])->match('/rows=(\d+)/');

            return isset($matches[1]) ? (int)$matches[1] : null;
        }

        return null;
    }

    private function hasJoinInQuery(OrmQueryBuilder|DbalQueryBuilder $queryBuilder): bool
    {
        return ($queryBuilder instanceof OrmQueryBuilder && \count($queryBuilder->getDQLPart('join')) > 0)
            || (
                $queryBuilder instanceof DbalQueryBuilder
                && \str_contains(\strtoupper($queryBuilder->getSQL()), 'JOIN')
            )
            || $this->hasJoinsInQuery;
    }
}
