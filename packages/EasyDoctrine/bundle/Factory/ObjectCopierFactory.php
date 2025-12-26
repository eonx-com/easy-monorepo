<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Bundle\Factory;

use DeepCopy\DeepCopy;
use DeepCopy\Filter\Doctrine\DoctrineCollectionFilter;
use DeepCopy\Matcher\PropertyTypeMatcher;
use Deprecated;
use Doctrine\Common\Collections\Collection;
use EonX\EasyDoctrine\DeepCopy\Filter\DoctrineInitializedCollectionDeepCopyFilter;
use EonX\EasyDoctrine\EntityEvent\Copier\ObjectCopier;
use EonX\EasyDoctrine\EntityEvent\Copier\ObjectCopierInterface;

final class ObjectCopierFactory
{
    #[Deprecated(message: 'since 6.0.3, will be removed in 7.0.0. Use createForDeletedEntity')]
    public static function create(): ObjectCopierInterface
    {
        $deepCopy = new DeepCopy();
        $deepCopy->addFilter(
            new DoctrineCollectionFilter(),
            new PropertyTypeMatcher(Collection::class)
        );

        return new ObjectCopier($deepCopy);
    }

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
