<?php
declare(strict_types=1);

namespace StepTheFkUp\MockBuilder\Implementations\Doctrine\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use StepTheFkUp\MockBuilder\AbstractMockBuilder;

/**
 * @method self hasClear(?string $objectName = null)
 * @method self hasContains($object)
 * @method self hasDetach($object)
 * @method self hasFind(string $className, $id)
 * @method self hasFlush()
 * @method self getClassMetadata(string $className)
 * @method self getMetadataFactory()
 * @method self hasGetRepository(string $className)
 * @method self initializeObject(\Closure|object $objectOrClosure)
 * @method self hasMerge(\Closure|object $objectOrClosure)
 * @method self hasPersist(\Closure|object $objectOrClosure)
 * @method self hasRefresh(\Closure|object $objectOrClosure)
 * @method self hasRemove(\Closure|object $objectOrClosure)
 *
 * @see \Doctrine\Common\Persistence\ObjectManager
 */
class ObjectManagerMockBuilder extends AbstractMockBuilder
{
    /**
     * Get class to mock.
     *
     * @return string
     */
    protected function getClassToMock(): string
    {
        return ObjectManager::class;
    }
}
