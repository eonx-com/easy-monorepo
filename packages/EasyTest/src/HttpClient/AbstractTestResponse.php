<?php
declare(strict_types=1);

namespace EonX\EasyTest\HttpClient;

use PHPUnit\Framework\Assert;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\Response\MockResponse;
use Throwable;
use UnexpectedValueException;

/**
 * @internal Use `self::arrangeHttpResponse(...)` or `self::arrangeHttpResponseWithTransportException(...)` instead
 */
abstract class AbstractTestResponse
{
    public const CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';

    public const CONTENT_TYPE_JSON = 'application/json';

    public const HEADER_ACCEPT = 'Accept';

    public const HEADER_CONTENT_LENGTH = 'Content-Length';

    public const HEADER_CONTENT_TYPE = 'Content-Type';

    protected readonly string $url;

    /**
     * @param array|string|\Symfony\Component\HttpClient\Exception\TransportException|null $responseData If null, empty array will be used for JSON or empty string otherwise
     * @param array<string, string>|null $responseHeaders
     * @param int|null $responseCode If null, 200 will be used
     */
    public function __construct(
        string $url,
        ?array $queryData = null,
        protected readonly array|string|TransportException|null $responseData = null,
        protected readonly ?array $responseHeaders = null,
        protected readonly ?int $responseCode = null,
    ) {
        if (\is_array($queryData)) {
            $queryString = \http_build_query($queryData, '', '&', \PHP_QUERY_RFC3986);

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

            $url .= '?' . $queryString;
        }

        $this->url = $url;
    }

    abstract public function __invoke(string $method, string $url, ?array $options = null): MockResponse;

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
            $acceptHeader = $options['normalized_headers'][\strtolower(self::HEADER_ACCEPT)][0] ?? null;

            if ($acceptHeader !== self::HEADER_ACCEPT . ': ' . self::CONTENT_TYPE_JSON) {
                TestResponseFactory::throwException(new UnexpectedValueException(\sprintf(
                    'Response body for "%s" is an array, but the Accept header is not "%s".',
                    $url,
                    self::CONTENT_TYPE_JSON,
                )));
            }

            $responseBody = (string)\json_encode($this->responseData);
        }

        return new MockResponse($responseBody, [
            'http_code' => $this->responseCode ?? 200,
            'response_headers' => $this->responseHeaders ?? [],
        ]);
    }
}
