<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Hydrator;

interface ObjectHydratorInterface
{
    /**
     * @template T of object
     *
     * @param T $object
     *
     * @return T
     */
    public function hydrate(object $object, array $sanitizedData): object;
}
