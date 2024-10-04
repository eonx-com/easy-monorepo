<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\DeepCopy\Filter;

use DeepCopy\Filter\Filter;
use DeepCopy\Reflection\ReflectionHelper;
use Doctrine\Common\Collections\AbstractLazyCollection;

final class DoctrineInitializedCollectionFilter implements Filter
{
    /**
     * @inheritdoc
     */
    public function apply($object, $property, $objectCopier): void
    {
        $reflectionProperty = ReflectionHelper::getProperty($object, $property);

        $oldCollection = $reflectionProperty->getValue($object);
        $newCollection = $oldCollection;

        if ($oldCollection instanceof AbstractLazyCollection && $oldCollection->isInitialized()) {
            $newCollection = $oldCollection->map(static fn ($item) => $objectCopier($item));
        }

        $reflectionProperty->setValue($object, $newCollection);
    }
}
