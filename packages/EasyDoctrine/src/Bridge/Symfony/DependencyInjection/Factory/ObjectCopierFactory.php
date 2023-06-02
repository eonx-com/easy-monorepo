<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\DependencyInjection\Factory;

use DeepCopy\DeepCopy;
use DeepCopy\Filter\Doctrine\DoctrineCollectionFilter;
use DeepCopy\Matcher\PropertyTypeMatcher;
use Doctrine\Common\Collections\Collection;
use EonX\EasyDoctrine\Interfaces\ObjectCopierInterface;
use EonX\EasyDoctrine\Utils\ObjectCopier;

final class ObjectCopierFactory
{
    public static function create(): ObjectCopierInterface
    {
        $deepCopy = new DeepCopy();
        $deepCopy->addFilter(
            new DoctrineCollectionFilter(),
            new PropertyTypeMatcher(Collection::class),
        );

        return new ObjectCopier($deepCopy);
    }
}
