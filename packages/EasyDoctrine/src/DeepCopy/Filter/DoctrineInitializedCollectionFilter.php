<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\DeepCopy\Filter;

use DeepCopy\Filter\Filter;
use DeepCopy\Reflection\ReflectionHelper;
use Doctrine\Common\Collections\AbstractLazyCollection;

final class DoctrineInitializedCollectionFilter implements Filter
{
    /**
     * @inheritDoc
     */
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = ReflectionHelper::getProperty($object, $property);

        /** @var \Doctrine\Common\Collections\Collection $oldCollection */
        $oldCollection = $reflectionProperty->getValue($object);
        $newCollection = $oldCollection;

        if ($oldCollection instanceof AbstractLazyCollection && $oldCollection->isInitialized()) {
            $newCollection = $oldCollection->map(
                function ($item) use ($objectCopier) {
                    return $objectCopier($item);
                }
            );
        }

        $reflectionProperty->setValue($object, $newCollection);
    }
}
