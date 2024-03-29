<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData;

interface ObjectHydratorInterface
{
    public function hydrate(object $object, array $sanitizedData): object;
}
