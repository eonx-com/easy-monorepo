<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\EntityEvent\Copier;

interface ObjectCopierInterface
{
    public function copy(object $object): object;
}
