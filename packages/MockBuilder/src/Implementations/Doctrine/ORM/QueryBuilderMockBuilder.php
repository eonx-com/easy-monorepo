<?php
declare(strict_types=1);

namespace StepTheFkUp\MockBuilder\Implmentations\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use StepTheFkUp\MockBuilder\AbstractMockBuilder;

/**
 * @method self hasAdd(string $dqlPartName, object|array $dqlPart, bool $append = false)
 * @method self hasAddSelect(int $count, ?array $expectation = null, $return = null, $exception = null)
 * @method self hasAddCriteria(\Doctrine\Common\Collections\Criteria $criteria)
 * @method self hasAddGroupBy(string $groupBy)
 * @method self hasAddOrderBy(string|\Doctrine\ORM\Query\Expr\OrderBy $sort, string $order = null)
 * @method self hasAndWhere(int $count, ?array $expectation = null, $return = null, $exception = null)
 * @method self hasExpr(int $count, ?array $expectation = null, $return = null, $exception = null)
 * @method self hasGroupBy(int $count, ?array $expectation = null, $return = null, $exception = null)
 * @method self hasGetQuery(int $count, ?array $expectation = null, $return = null, $exception = null)
 * @method self hasInnerJoin(int $count, ?array $expectation = null, $return = null, $exception = null)
 * @method self hasLeftJoin(int $count, ?array $expectation = null, $return = null, $exception = null)
 * @method self hasOrderBy(int $count, ?array $expectation = null, $return = null, $exception = null)
 * @method self hasOrWhere(int $count, ?array $expectation = null, $return = null, $exception = null)
 * @method self hasSelect(int $count, ?array $expectation = null, $return = null, $exception = null)
 * @method self hasSetParameter(int $count, ?array $expectation = null, $return = null, $exception = null)
 * @method self hasSetParameters(int $count, ?array $expectation = null, $return = null, $exception = null)
 * @method self hasWhere(int $count, ?array $expectation = null, $return = null, $exception = null)
 *
 * @see \Doctrine\ORM\QueryBuilder
 */
final class QueryBuilderMockBuilder extends AbstractMockBuilder
{
    /**
     * Get class to mock.
     *
     * @return string
     */
    protected function getClassToMock(): string
    {
        return QueryBuilder::class;
    }
}
