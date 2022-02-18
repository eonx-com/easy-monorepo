<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Utils;

use DeepCopy\DeepCopy;
use DeepCopy\Filter\Doctrine\DoctrineCollectionFilter;
use DeepCopy\Matcher\PropertyTypeMatcher;
use Doctrine\Common\Collections\Collection;
use EonX\EasyDoctrine\Interfaces\ObjectCopierInterface;

class ObjectCopier implements ObjectCopierInterface
{
    /**
     * @var \DeepCopy\DeepCopy
     */
    private $copier;

    public function __construct()
    {
        $this->copier = new DeepCopy();
        $this->copier->addFilter(
            new DoctrineCollectionFilter(),
            new PropertyTypeMatcher(Collection::class)
        );
    }

    public function copy(object $object): object
    {
        return $this->copier->copy($object);
    }
}
