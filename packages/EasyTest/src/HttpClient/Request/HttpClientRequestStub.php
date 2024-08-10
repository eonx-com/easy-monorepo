<?php
declare(strict_types=1);

namespace EonX\EasyTest\HttpClient\Request;

use Closure;
use EonX\EasyTest\HttpClient\HttpClient\HttpClientStub;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @deprecated Since 6.0, will be removed in 7.0. Use TestResponseFactory instead.
 */
final class HttpClientRequestStub
{
    private ?string $hash = null;

    public function __construct(
        private Closure $addResponseClosure,
        private readonly string $method,
        private readonly string $url,
        private ?array $options = null,
    ) {
    }

    public function addResponse(array|string $data, ?array $info = null, ?int $count = null): HttpClientStub
    {
        $addResponseClosure = $this->addResponseClosure;

        $body = \is_array($data) ? (string)\json_encode($data) : $data;

        $count ??= 1;
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
