<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer\Interfaces;

interface QueueEnvelopeInterface
{
    public function getBody(): ?array;

    public function getHeader(string $header, mixed $default = null): mixed;

    public function getHeaders(): array;

    public function getOriginalBody(): string;
}
