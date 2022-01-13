<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony\Serializers;

interface CircularReferenceHandlerInterface
{
    /**
     * @param mixed[] $context
     *
     * @return mixed
     */
    public function __invoke(object $object, string $format, array $context);
}
