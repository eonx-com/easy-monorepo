<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface NormalizerInterface
{
    /**
     * @param mixed $object
     *
     * @return mixed
     */
    public function normalize($object);
}
