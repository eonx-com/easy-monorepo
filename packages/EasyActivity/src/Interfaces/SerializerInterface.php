<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface SerializerInterface
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $context
     *
     * @return string|null
     */
    public function serialize(array $data, array $context): ?string;
}
