<?php
declare(strict_types=1);

namespace EonX\EasyTest\HttpClient\Response;

use BackedEnum;
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
        protected ?array $query = null,
        protected readonly array|string|TransportException|null $responseData = null,
        protected ?array $responseHeaders = null,
        protected ?int $responseCode = null,
    ) {
        $this->query ??= [];
        $this->responseHeaders ??= [];
        $this->responseCode ??= 200;
    }

    final public function __invoke(string $method, string $url, ?array $options = null): MockResponse
    {
        $options ??= [];

        $this->checkParameters($method, $url, $options);

        return $this->createResponse($method, $url, $options);
    }

    protected static function normalizeData(array &$array): void
    {
        foreach ($array as &$value) {
            if (\is_array($value)) {
                self::normalizeData($value);
            }

            if (\is_array($value) === false) {
                if ($value instanceof BackedEnum) {
                    $value = $value->value;

                    continue;
                }

                $value = \is_scalar($value) ? $value : (string)$value;
            }
        }
        unset($value);

        \array_is_list($array) ? \sort($array) : \ksort($array);
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
            $expectedUrl = \parse_url($this->url);
            Assert::assertNotFalse($expectedUrl, \sprintf('Invalid URL for %s', $this->url));
            $actualUrl = \parse_url($url);
            Assert::assertNotFalse($actualUrl, \sprintf('Invalid URL for %s', $url));

            Assert::assertSame(
                $expectedUrl['scheme'] ?? '',
                $actualUrl['scheme'] ?? '',
                \sprintf('URL scheme for %s does not match', $this->url)
            );
            Assert::assertSame(
                $expectedUrl['host'] ?? '',
                $actualUrl['host'] ?? '',
                \sprintf('URL host for %s does not match', $this->url)
            );
            Assert::assertSame(
                $expectedUrl['port'] ?? '',
                $actualUrl['port'] ?? '',
                \sprintf('URL port for %s does not match', $this->url)
            );
            Assert::assertSame(
                $expectedUrl['user'] ?? '',
                $actualUrl['user'] ?? '',
                \sprintf('URL user for %s does not match', $this->url)
            );
            Assert::assertSame(
                $expectedUrl['pass'] ?? '',
                $actualUrl['pass'] ?? '',
                \sprintf('URL pass for %s does not match', $this->url)
            );
            Assert::assertSame(
                $expectedUrl['path'] ?? '',
                $actualUrl['path'] ?? '',
                \sprintf('URL path for %s does not match', $this->url)
            );
            Assert::assertSame(
                $expectedUrl['fragment'] ?? '',
                $actualUrl['fragment'] ?? '',
                \sprintf('URL fragment for %s does not match', $this->url)
            );

            \parse_str($expectedUrl['query'] ?? '', $expectedQuery);
            \parse_str($actualUrl['query'] ?? '', $actualQuery);

            $expectedQuery = [
                ...$expectedQuery,
                ...($this->query ?? []),
            ];

            self::normalizeData($expectedQuery);
            self::normalizeData($actualQuery);

            Assert::assertSame($expectedQuery, $actualQuery, \sprintf('URL query for %s does not match', $this->url));
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
