<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\EntityEvent\Copier;

use DeepCopy\DeepCopy;

final readonly class ObjectCopier implements ObjectCopierInterface
{
    public function __construct(
        private DeepCopy $deepCopy,
    ) {
    }

    public function copy(object $object): object
    {
        return $this->deepCopy->copy($object);
    }
}
