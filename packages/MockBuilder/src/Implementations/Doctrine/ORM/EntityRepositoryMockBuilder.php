<?php
declare(strict_types=1);

namespace StepTheFkUp\MockBuilder\Implementations\Doctrine\ORM;

use Doctrine\ORM\EntityRepository;

/**
 * @method self hasClear()
 * @method self hasCount(array $criteria)
 * @method self hasCreateNamedQuery($queryName)
 * @method self hasCreateNativeNamedQuery($queryName)
 * @method self hasCreateQueryBuilder(string $alias, ?string $indexBy = null)
 * @method self hasCreateResultSetMappingBuilder(string $alias)
 * @method self hasGetMetadataFactory()
 * @method self hasMatching(\Doctrine\Common\Collections\Criteria $criteria)
 *
 * @see \Doctrine\ORM\EntityRepository
 */
final class EntityRepositoryMockBuilder extends ObjectManagerMockBuilder
{
    /**
     * Get class to mock.
     *
     * @return string
     */
    protected function getClassToMock(): string
    {
        return EntityRepository::class;
    }
}
