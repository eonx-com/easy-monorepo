<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Utils;

use DeepCopy\DeepCopy;
use DeepCopy\Filter\Doctrine\DoctrineCollectionFilter;
use DeepCopy\Matcher\PropertyTypeMatcher;
use Doctrine\Common\Collections\Collection;
use EonX\EasyDoctrine\Interfaces\ObjectCopierFactoryInterface;
use EonX\EasyDoctrine\Interfaces\ObjectCopierInterface;

final class ObjectCopierFactory implements ObjectCopierFactoryInterface
{
    public function create(): ObjectCopierInterface
    {
        $deepCopy = new DeepCopy();
        $deepCopy->addFilter(
            new DoctrineCollectionFilter(),
            new PropertyTypeMatcher(Collection::class)
        );

        return new ObjectCopier($deepCopy);
    }
}
