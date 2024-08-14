<?php
declare(strict_types=1);

namespace EonX\EasyTest\HttpClient\Response;

use EonX\EasyTest\HttpClient\Factory\TestResponseFactory;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\Response\MockResponse;
use Throwable;

abstract class AbstractTestResponse
{
    protected array $headers;

    /**
     * @param array|string|\Symfony\Component\HttpClient\Exception\TransportException|null $responseData If null, empty array will be used for JSON or empty string otherwise
     * @param array<string, string>|null $responseHeaders
     * @param int|null $responseCode If null, 200 will be used
     */
    public function __construct(
        protected string $url,
        ?array $query = null,
        protected readonly array|string|TransportException|null $responseData = null,
        protected ?array $responseHeaders = null,
        protected ?int $responseCode = null,
    ) {
        if (\is_array($query)) {
            $queryString = \http_build_query($query, '', '&', \PHP_QUERY_RFC3986);

            if (\str_contains($queryString, '%')) {
                $queryString = \strtr($queryString, [
                    '%21' => '!',
                    '%24' => '$',
                    '%28' => '(',
                    '%29' => ')',
                    '%2A' => '*',
                    '%2F' => '/',
                    '%3A' => ':',
                    '%3B' => ';',
                    '%40' => '@',
                    '%5B' => '[',
                    '%5D' => ']',
                ]);
            }

            $this->url .= '?' . $queryString;
        }

        $this->responseHeaders ??= [];
        $this->responseCode ??= 200;
    }

    final public function __invoke(string $method, string $url, ?array $options = null): MockResponse
    {
        $options ??= [];

        $this->checkParameters($method, $url, $options);

        return $this->createResponse($method, $url, $options);
    }

    abstract protected function checkParameters(string $method, string $url, array $options): void;

    protected function checkHeaders(array $options): void
    {
        $normalizedHeaders = $this->getNormalizeHeaders($options);

        \ksort($normalizedHeaders);
        \ksort($options['normalized_headers']);

        try {
            Assert::assertSame(
                $normalizedHeaders,
                $options['normalized_headers'],
                \sprintf('Request headers for %s do not match', $this->url)
            );
        } catch (Throwable $exception) {
            TestResponseFactory::throwException($exception);
        }
    }

    protected function checkUrl(string $url): void
    {
        try {
            Assert::assertSame($this->url, $url, \sprintf('Request URL for %s does not match.', $url));
        } catch (Throwable $exception) {
            TestResponseFactory::throwException($exception);
        }
    }

    protected function createResponse(string $method, string $url, array $options): MockResponse
    {
        if ($this->responseData instanceof TransportException) {
            throw $this->responseData;
        }

        $responseBody = '';

        if (\is_string($this->responseData)) {
            $responseBody = $this->responseData;
        }

        if ($this->responseData === null) {
            $responseBody = '[]';
        }

        if (\is_array($this->responseData)) {
            $responseBody = (string)\json_encode($this->responseData);
        }

        return new MockResponse($responseBody, [
            'http_code' => $this->responseCode,
            'response_headers' => $this->responseHeaders,
        ]);
    }

    protected function getNormalizeHeaders(array $options): array
    {
        $normalizedHeaders = [];
        foreach ($this->headers as $header => $value) {
            $normalizedHeaders[\strtolower($header)] = [\sprintf('%s: %s', $header, $value)];
        }

        return \array_merge($options['normalized_headers'], $normalizedHeaders);
    }
}
