<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony\Stubs;

use Symfony\Component\Serializer\SerializerInterface;

final class SerializerStub implements SerializerInterface
{
    /**
     * @param mixed[]|null $context
     *
     * @return mixed[]
     */
    public function deserialize(mixed $data, string $type, string $format, ?array $context = null): array
    {
        return [];
    }

    /**
     * @param mixed[]|null $context
     */
    public function serialize(mixed $data, string $format, ?array $context = null): string
    {
        return '';
    }
}
