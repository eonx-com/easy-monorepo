<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Hydrator;

interface ObjectHydratorInterface
{
    public function hydrate(object $object, array $sanitizedData): object;
}
