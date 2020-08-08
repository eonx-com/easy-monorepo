<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stubs;

use Symfony\Component\HttpClient\Response\MockResponse;
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
     * @var string
     */
    private $url;

    public function getMethod(): ?string
    {
        return $this->method;
    }

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
     */
    public function request(string $method, string $url, ?array $options = null): ResponseInterface
    {
        $this->method = $method;
        $this->url = $url;
        $this->options = $options;

        return new MockResponse();
    }

    /**
     * Yields responses chunk by chunk as they complete.
     *
     * @param ResponseInterface|ResponseInterface[]|iterable $responses One or more responses created by the current
     *     HTTP client
     * @param float|null $timeout The idle timeout before yielding timeout chunks
     */
    public function stream($responses, ?float $timeout = null): ResponseStreamInterface
    {
        // No body needed.
    }
}
