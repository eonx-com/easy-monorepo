<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Utils;

use DeepCopy\DeepCopy;
use EonX\EasyDoctrine\Interfaces\ObjectCopierInterface;

final class ObjectCopier implements ObjectCopierInterface
{
    public function __construct(
        private DeepCopy $deepCopy
    ) {
    }

    public function copy(object $object): object
    {
        return $this->deepCopy->copy($object);
    }
}
