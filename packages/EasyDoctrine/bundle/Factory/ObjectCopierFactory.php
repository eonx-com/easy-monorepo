<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Bundle\Factory;

use DeepCopy\DeepCopy;
use DeepCopy\Matcher\PropertyTypeMatcher;
use Doctrine\Common\Collections\Collection;
use EonX\EasyDoctrine\DeepCopy\Filter\DoctrineInitializedCollectionDeepCopyFilter;
use EonX\EasyDoctrine\EntityEvent\Copier\ObjectCopier;
use EonX\EasyDoctrine\EntityEvent\Copier\ObjectCopierInterface;

final class ObjectCopierFactory
{
    public static function createForDeletedEntity(): ObjectCopierInterface
    {
        $deepCopy = new DeepCopy();
        $deepCopy->addFilter(
            new DoctrineInitializedCollectionDeepCopyFilter(),
            new PropertyTypeMatcher(Collection::class)
        );

        return new ObjectCopier($deepCopy);
    }
}
