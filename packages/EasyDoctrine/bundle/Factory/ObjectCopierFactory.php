<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Bundle\Factory;

use DeepCopy\DeepCopy;
use DeepCopy\Matcher\PropertyTypeMatcher;
use Doctrine\Common\Collections\Collection;
use EonX\EasyDoctrine\DeepCopy\Filter\DoctrineInitializedCollectionFilter;
use EonX\EasyDoctrine\EntityEvent\Copier\ObjectCopier;
use EonX\EasyDoctrine\EntityEvent\Copier\ObjectCopierInterface;

final class ObjectCopierFactory
{
    public static function create(): ObjectCopierInterface
    {
        $deepCopy = new DeepCopy();
        $deepCopy->addFilter(
            new DoctrineInitializedCollectionFilter(),
            new PropertyTypeMatcher(Collection::class)
        );

        return new ObjectCopier($deepCopy);
    }
}
