<?php

declare(strict_types=1);

namespace EonX\EasyTest\Stub\HttpClient;

use Closure;
use Symfony\Component\HttpClient\Response\MockResponse;

final class HttpClientRequestStub
{
    private ?string $hash = null;

    /**
     * @param mixed[]|null $options
     */
    public function __construct(
        private Closure $addResponseClosure,
        private string $method,
        private string $url,
        private ?array $options = null
    ) {
    }

    /**
     * @param mixed[]|string $data
     * @param mixed[]|null $info
     */
    public function addResponse(array|string $data, ?array $info = null, ?int $count = null): HttpClientStub
    {
        $addResponseClosure = $this->addResponseClosure;

        $body = \is_array($data) ? (string)\json_encode($data) : $data;

        $count = $count ?? 1;
        do {
            $httpClient = $addResponseClosure(new MockResponse($body, $info ?? []), $this->getHash());
        } while ($count-- > 1);

        return $httpClient;
    }

    public function getHash(): string
    {
        $headers = $this->options['headers'] ?? [];
        \sort($headers);

        return $this->hash ??= \sha1((string)\json_encode([
            $this->method,
            $this->url,
            $headers,
            $this->options['body'] ?? null,
        ]));
    }
}
