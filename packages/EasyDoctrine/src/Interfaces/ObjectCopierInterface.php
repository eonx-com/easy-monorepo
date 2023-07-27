<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Interfaces;

interface ObjectCopierInterface
{
    public function copy(object $object): object;
}
