<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer\Interfaces;

interface QueueEnvelopeInterface
{
    /**
     * @return mixed[]
     */
    public function getBody(): ?array;

    public function getOriginalBody(): string;

    /**
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function getHeader(string $header, $default = null);

    /**
     * @return mixed[]
     */
    public function getHeaders(): array;
}
