<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stubs;

use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpClient\Response\ResponseStream;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

final class HttpClientStub implements HttpClientInterface
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var null|mixed[]
     */
    private $options;

    /**
     * @var null|\Throwable
     */
    private $throwable;

    /**
     * @var string
     */
    private $url;

    public function __construct(?\Throwable $throwable = null)
    {
        $this->throwable = $throwable;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * @return null|mixed[]
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param null|mixed[] $options
     *
     * @throws \Throwable
     */
    public function request(string $method, string $url, ?array $options = null): ResponseInterface
    {
        $this->method = $method;
        $this->url = $url;
        $this->options = $options;

        if ($this->throwable !== null) {
            throw $this->throwable;
        }

        return MockResponse::fromRequest($method, $url, $options ?? [], new MockResponse());
    }

    /**
     * Yields responses chunk by chunk as they complete.
     *
     * @param \Symfony\Contracts\HttpClient\ResponseInterface|\Symfony\Contracts\HttpClient\ResponseInterface[]|iterable $responses One or more responses created by the current HTTP client
     * @param float|null $timeout The idle timeout before yielding timeout chunks
     */
    public function stream($responses, ?float $timeout = null): ResponseStreamInterface
    {
        return new ResponseStream($this->getGenerator());
    }

    /**
     * @return \Generator<string>
     */
    private function getGenerator(): \Generator
    {
        yield 'stream';
    }
}
