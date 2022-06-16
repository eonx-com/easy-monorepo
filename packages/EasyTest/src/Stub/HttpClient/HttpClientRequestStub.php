<?php

declare(strict_types=1);

namespace EonX\EasyTest\Stub\HttpClient;

use Closure;
use Symfony\Component\HttpClient\Response\MockResponse;

final class HttpClientRequestStub
{
    private Closure $addResponseClosure;

    private ?string $hash = null;

    private string $method;

    /**
     * @var mixed[]|null
     */
    private ?array $options = null;

    private string $url;

    /**
     * @param mixed[]|null $options
     */
    public function __construct(
        Closure $addResponseClosure,
        string $method,
        string $url,
        ?array $options = null
    ) {
        $this->addResponseClosure = $addResponseClosure;
        $this->method = $method;
        $this->url = $url;
        $this->options = $options;
    }

    /**
     * @param mixed[]|string $data
     * @param mixed[]|null $info
     */
    public function addResponse(array|string $data, ?array $info = null): HttpClientStub
    {
        $addResponseClosure = $this->addResponseClosure;

        $body = \is_array($data) ? (string)\json_encode($data) : $data;

        return $addResponseClosure(new MockResponse($body, $info ?? []), $this->getHash());
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
