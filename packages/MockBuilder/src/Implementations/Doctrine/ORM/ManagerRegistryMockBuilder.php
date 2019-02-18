<?php
declare(strict_types=1);

namespace StepTheFkUp\MockBuilder\Implementations\Doctrine\ORM;

use Doctrine\Common\Persistence\ManagerRegistry;
use StepTheFkUp\MockBuilder\AbstractMockBuilder;

/**
 * @method self hasGetAliasNamespace(string $alias)
 * @method self hasGetDefaultManagerName()
 * @method self hasGetManager(?string $name = null)
 * @method self hasGetManagerForClass(string $class)
 * @method self hasGetManagerNames()
 * @method self hasGetManagers(string $className)
 * @method self hasGetRepository(string $persistentObject, ?string $persistentManagerName = null)
 * @method self hasResetManager(?string $name = null)
 *
 * @see \Doctrine\Common\Persistence\ManagerRegistry
 */
class ManagerRegistryMockBuilder extends AbstractMockBuilder
{
    /**
     * Get class to mock.
     *
     * @return string
     */
    protected function getClassToMock(): string
    {
        return ManagerRegistry::class;
    }
}
