<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer;

use EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer\Interfaces\QueueEnvelopeInterface;

final class QueueEnvelope implements QueueEnvelopeInterface
{
    /**
     * @var null|mixed[]
     */
    private $body;

    /**
     * @var mixed[]
     */
    private $headers;

    /**
     * @var string
     */
    private $originalBody;

    /**
     * @param mixed[] $headers
     * @param null|mixed[] $body
     */
    public function __construct(string $originalBody, array $headers, ?array $body = null)
    {
        $this->originalBody = $originalBody;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * @param mixed[] $headers
     * @param null|mixed[] $body
     */
    public static function create(string $originalBody, array $headers, ?array $body = null): QueueEnvelopeInterface
    {
        return new self($originalBody, $headers, $body);
    }

    public function getBody(): ?array
    {
        return $this->body;
    }

    /**
     * @param null|mixed $default
     *
     * @return null|mixed
     */
    public function getHeader(string $header, $default = null)
    {
        return $this->headers[$header] ?? $default;
    }

    /**
     * @return mixed[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getOriginalBody(): string
    {
        return $this->originalBody;
    }
}
