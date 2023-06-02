<?php

declare(strict_types=1);

namespace EonX\EasyTest\Stub\HttpClient;

use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\HttpClientTrait;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

class HttpClientStub extends MockHttpClient
{
    use HttpClientTrait;

    private const DEFAULT_REQUEST_HEADERS = ['Accept: */*'];

    protected ?MockResponse $defaultResponse = null;

    /**
     * @var \Symfony\Component\HttpClient\Response\MockResponse[][]
     */
    protected array $responses = [];

    private ?Throwable $expectedException = null;

    public function __construct(
        protected string $baseUri = 'https://example.com',
    ) {
        parent::__construct(
            function ($method, $url, $options): ResponseInterface {
                return $this->getResponse($method, $url, $options);
            },
            $baseUri
        );
    }

    /**
     * @param mixed[] $headers
     * @param mixed[] $body
     * @param mixed[] $queryParams
     */
    public function forRequest(
        string $method,
        string $url,
        ?array $headers = null,
        ?array $body = null,
        ?array $queryParams = null,
    ): HttpClientRequestStub {
        $url = $this->normalizeUrl($url);
        $options = [];
        $options['headers'] = \array_unique(\array_merge($headers ?? [], self::DEFAULT_REQUEST_HEADERS));
        if ($body !== null) {
            $contentType = null;
            foreach ($options['headers'] as $header) {
                if (\str_starts_with($header, 'Content-Type')) {
                    $contentType = \str_replace('Content-Type: ', '', $header);

                    break;
                }
            }
            if ($contentType === null) {
                $options['headers'][] = 'Content-Type: application/json';
            }
            $options['body'] = match ($contentType) {
                'application/x-www-form-urlencoded' => \http_build_query($body),
                default => self::jsonEncode($body),
            };
            $options['headers'][] = 'Content-Length: ' . \strlen($options['body']);
        }

        if ($queryParams !== null) {
            $url = \sprintf('%s?%s', $url, \http_build_query($queryParams));
        }

        return new HttpClientRequestStub(
            function (MockResponse $response, string $requestHash): self {
                $this->responses[$requestHash] ??= [];
                $this->responses[$requestHash][] = $response;

                return $this;
            },
            $method,
            $url,
            $options
        );
    }

    public function forRequestWithAnyOptions(string $method, string $url): HttpClientRequestStub
    {
        $url = $this->normalizeUrl($url);

        return new HttpClientRequestStub(
            function (MockResponse $response, string $requestHash): self {
                $this->responses[$requestHash] ??= [];
                $this->responses[$requestHash][] = $response;

                return $this;
            },
            $method,
            $url,
        );
    }

    public function getDefaultResponse(): MockResponse
    {
        return $this->defaultResponse ?? new MockResponse('');
    }

    /**
     * @param mixed[] $options
     */
    public function getResponse(string $method, string $url, ?array $options = null): MockResponse
    {
        $url = $this->normalizeUrl($url);
        $request = new HttpClientRequestStub(fn (): self => $this, $method, $url, $options);

        if (\count($this->responses[$request->getHash()] ?? []) > 0) {
            /** @var \Symfony\Component\HttpClient\Response\MockResponse $response */
            $response = \array_shift($this->responses[$request->getHash()]);

            return $response;
        }

        $requestWithoutOptions = new HttpClientRequestStub(fn (): self => $this, $method, $url);
        if (\count($this->responses[$requestWithoutOptions->getHash()] ?? []) > 0) {
            /** @var \Symfony\Component\HttpClient\Response\MockResponse $response */
            $response = \array_shift($this->responses[$requestWithoutOptions->getHash()]);

            return $response;
        }

        return $this->getDefaultResponse();
    }

    public function hasUnusedResponses(): bool
    {
        foreach ($this->responses as $requestResponses) {
            if (\count($requestResponses) > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed[]|null $options
     */
    public function request(string $method, string $url, ?array $options = null): ResponseInterface
    {
        if ($this->expectedException !== null) {
            throw $this->expectedException;
        }

        $url = $this->normalizeUrl($url);

        return parent::request($method, $url, $options ?? []);
    }

    /**
     * @param mixed[] $body
     */
    public function setDefaultResponse(array $body): void
    {
        $this->defaultResponse = new MockResponse((string)\json_encode($body));
    }

    public function willThrowException(?Throwable $expectedException = null): self
    {
        $this->expectedException = $expectedException
            ?? new TransportException('This is an expected exception.');

        return $this;
    }

    protected function normalizeUrl(string $url): string
    {
        $urlInfo = \parse_url($url);

        if (\is_array($urlInfo) && isset($urlInfo['host'])) {
            return $url;
        }

        return \rtrim($this->baseUri, '/') . '/' . \ltrim($url, '/');
    }
}
