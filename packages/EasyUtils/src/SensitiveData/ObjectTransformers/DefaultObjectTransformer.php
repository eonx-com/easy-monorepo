<?php

declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\ObjectTransformers;

use DateTimeInterface;

final class DefaultObjectTransformer extends AbstractObjectTransformer
{
    public function supports(object $object): bool
    {
        return $object instanceof DateTimeInterface === false;
    }

    public function transform(object $object): array
    {
        return (array)\json_decode((string)\json_encode($object), true);
    }
}
