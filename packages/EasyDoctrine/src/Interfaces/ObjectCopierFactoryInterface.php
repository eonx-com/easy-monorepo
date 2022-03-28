<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Interfaces;

interface ObjectCopierFactoryInterface
{
    public function create(): ObjectCopierInterface;
}
