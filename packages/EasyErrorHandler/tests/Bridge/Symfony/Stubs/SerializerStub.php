<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony\Stubs;

use Symfony\Component\Serializer\SerializerInterface;

final class SerializerStub implements SerializerInterface
{
    /**
     * @param mixed $data
     * @param mixed $format
     * @param mixed[]|null $context
     */
    public function serialize($data, $format, ?array $context = null): string
    {
        return '';
    }

    /**
     * @param mixed $data
     * @param string $type
     * @param string $format
     * @param mixed[]|null $context
     *
     * @return mixed[]
     */
    public function deserialize($data, $type, $format, ?array $context = null): array
    {
        return [];
    }
}
