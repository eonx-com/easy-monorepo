<?php
declare(strict_types=1);

namespace StepTheFkUp\MockBuilder\Implementations\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @method self hasBeginTransaction()
 * @method self hasClose()
 * @method self hasCommit()
 * @method self hasCopy(object $entity, bool $deep = false)()
 * @method self hasCreateNamedNativeQuery(string $name)
 * @method self hasCreateNamedQuery(string $name)
 * @method self hasCreateNativeQuery(string $sql, \Doctrine\ORM\Query\ResultSetMapping $rsm)
 * @method self hasCreateQuery(string $dql = '');
 * @method self hasCreateQueryBuilder()
 * @method self hasGetCache()
 * @method self hasGetConfiguration()
 * @method self hasGetConnection()
 * @method self hasGetEventManager()
 * @method self hasGetExpressionBuilder()
 * @method self hasGetFilters()
 * @method self hasGetHydrator(string|int $hydrationMode)
 * @method self hasGetPartialReference(string $entityName, mixed $identifier)
 * @method self hasGetProxyFactory()
 * @method self hasGetReference(string $entityName, mixed $id)
 * @method self hasGetUnitOfWork()
 * @method self hasHasFilters()
 * @method self hasIsFiltersStateClean()
 * @method self hasIsOpen()
 * @method self hasLock(object $entity, int $lockMode, null|int $lockVersion = null)
 * @method self hasNewHydrator(string|int $hydrationMode)
 * @method self hasRollback()
 * @method self hasTransactional(callable $func)
 *
 * @see \Doctrine\ORM\EntityManagerInterface
 */
final class EntityManagerMockBuilder extends ObjectManagerMockBuilder
{
    /**
     * Get class to mock.
     *
     * @return string
     */
    protected function getClassToMock(): string
    {
        return EntityManagerInterface::class;
    }
}
