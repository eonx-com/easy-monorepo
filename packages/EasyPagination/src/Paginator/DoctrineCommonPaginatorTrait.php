<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

use BackedEnum;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Query\QueryBuilder as DbalQueryBuilder;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\QueryBuilder as OrmQueryBuilder;

use function Symfony\Component\String\u;

trait DoctrineCommonPaginatorTrait
{
    use DatabaseCommonPaginatorTrait;

    private ?string $fromAlias = null;

    private int $largeDatasetPaginationPreciseResultsLimit = 100_000;

    private ?int $totalItems = null;

    public function getLargeDatasetPaginationPreciseResultsLimit(): int
    {
        return $this->largeDatasetPaginationPreciseResultsLimit;
    }

    public function setLargeDatasetPaginationPreciseResultsLimit(int $largeDatasetPaginationPreciseResultsLimit): self
    {
        $this->largeDatasetPaginationPreciseResultsLimit = $largeDatasetPaginationPreciseResultsLimit;

        return $this;
    }

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
            $approximateTotalItems = $this->getTotalItemsForLargeDataset($queryBuilder);

            if ($approximateTotalItems !== null &&
                $approximateTotalItems > $this->getLargeDatasetPaginationPreciseResultsLimit()
            ) {
                return $this->totalItems = $approximateTotalItems;
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
        /** @var string|string[]|null $select */
        $select = $this->resolveSelect();
        $queryBuilder = $this->createQueryBuilder();

        if ($select !== null) {
            $select = \is_array($select) ? $select : [$select];
            $queryBuilder->select(...$select);
        }

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

        /** @var array<int|string> $primaryKeys */
        $primaryKeys = \array_column($this->fetchResults($fetchPrimaryKeysQueryBuilder), $primaryKeyIndex);

        // If no primary keys, no items for current pagination
        if (\count($primaryKeys) === 0) {
            return [];
        }

        // Filter records on their primary keys. DBAL puts the values of in() into the SQL as they are, so the keys
        // are bound: a stored key could otherwise inject SQL, and a non-numeric key (e.g. UUID) breaks the query
        if ($queryBuilder instanceof DbalQueryBuilder) {
            $isIntegerKeys = \array_filter($primaryKeys, \is_int(...)) === $primaryKeys;
            $primaryKeysParameter = $queryBuilder->createNamedParameter(
                $isIntegerKeys ? $primaryKeys : \array_map(\strval(...), $primaryKeys),
                $isIntegerKeys ? ArrayParameterType::INTEGER : ArrayParameterType::STRING
            );
            $queryBuilder->andWhere($queryBuilder->expr()->in($select, $primaryKeysParameter));
        } else {
            $queryBuilder->andWhere($queryBuilder->expr()->in($select, \array_map(\strval(...), $primaryKeys)));
        }

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
            && $platform instanceof SqlitePlatform === false) {
            return null;
        }

        $queryBuilder->select('1');

        $sql = null;
        $params = [];
        /** @var array<array-key, \Doctrine\DBAL\Types\Type|int|string|null> $paramTypes */
        $paramTypes = [];

        if ($queryBuilder instanceof OrmQueryBuilder) {
            $query = $queryBuilder->getQuery();
            $sql = $query->getSQL();

            $parametersMap = [];
            $paramTypesMap = [];
            foreach ($queryBuilder->getParameters() as $param) {
                $parametersMap[$param->getName()] = $param->getValue();
                $paramTypesMap[$param->getName()] = $param->getType();
            }

            $paramMappings = (new Parser($query))->parse()
                ->getParameterMappings();
            foreach ($paramMappings as $paramName => $positions) {
                if (\array_key_exists($paramName, $parametersMap) === false) {
                    continue;
                }
                foreach ($positions as $position) {
                    $params[$position] = $parametersMap[$paramName];
                    $paramTypes[$position] = $paramTypesMap[$paramName];
                }
            }
            \ksort($params);
            \ksort($paramTypes);
            $params = \array_values($params);
            $paramTypes = \array_values($paramTypes);
        }

        if ($queryBuilder instanceof DbalQueryBuilder) {
            $sql = $queryBuilder->getSQL();
            $params = $queryBuilder->getParameters();
            $paramTypes = $queryBuilder->getParameterTypes();
        }

        foreach ($params as $key => $param) {
            if ($param instanceof BackedEnum) {
                $params[$key] = $param->value;
                $paramTypes[$key] = \is_int($param->value) ? ParameterType::INTEGER : ParameterType::STRING;
            }
        }

        if (\is_string($sql) === false) {
            return null;
        }

        if ($platform instanceof PostgreSQLPlatform) {
            $sql = \sprintf('EXPLAIN %s', $sql);
        }
        if ($platform instanceof SqlitePlatform) {
            $sql = \sprintf('EXPLAIN QUERY PLAN %s', $sql);
        }

        $result = $connection->executeQuery($sql, $params, $paramTypes)
            ->fetchAssociative();

        if (\is_array($result) && isset($result['QUERY PLAN'])) {
            /** @var string $queryPlan */
            $queryPlan = $result['QUERY PLAN'];
            $matches = u($queryPlan)
                ->match('/rows=(\d+)/');

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
