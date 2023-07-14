<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer\Interfaces;

interface QueueEnvelopeInterface
{
    /**
     * @return mixed[]|null
     */
    public function getBody(): ?array;

    public function getHeader(string $header, mixed $default = null): mixed;

    /**
     * @return mixed[]
     */
    public function getHeaders(): array;

    public function getOriginalBody(): string;
}
