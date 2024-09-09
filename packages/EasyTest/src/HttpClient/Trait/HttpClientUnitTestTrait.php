<?php
declare(strict_types=1);

namespace EonX\EasyTest\HttpClient\Trait;

use EonX\EasyTest\HttpClient\Factory\TestResponseFactory;
use EonX\EasyTest\HttpClient\Response\StrictTestResponse;
use Symfony\Component\HttpClient\Exception\TransportException;

trait HttpClientUnitTestTrait
{
    use HttpClientCommonTestTrait;

    /**
     * @param \Symfony\Component\HttpFoundation\Request::METHOD_* $method
     * @param array<string, string>|null $headers
     * @param array|string|null $responseData If null, empty array will be used for JSON or empty string otherwise
     * @param array<string, string>|null $responseHeaders
     * @param int|null $responseCode If null, 200 will be used
     *
     * @noinspection PhpTooManyParametersInspection
     */
    protected static function arrangeHttpResponse(
        string $method,
        string $url,
        ?array $query = null,
        ?array $body = null,
        ?array $json = null,
        ?array $headers = null,
        array|string|null $responseData = null,
        ?array $responseHeaders = null,
        ?int $responseCode = null,
        ?int $requestsCount = null,
    ): void {
        for ($counter = 0; $counter < ($requestsCount ?? 1); ++$counter) {
            TestResponseFactory::addResponse(
                new StrictTestResponse(
                    method: $method,
                    url: $url,
                    query: $query,
                    body: $body,
                    json: $json,
                    headers: $headers,
                    responseData: $responseData,
                    responseHeaders: $responseHeaders,
                    responseCode: $responseCode,
                )
            );
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request::METHOD_* $method
     * @param array<string, string>|null $headers
     *
     * @noinspection PhpTooManyParametersInspection
     */
    protected static function arrangeHttpResponseWithTransportException(
        string $method,
        string $url,
        ?array $query = null,
        ?array $body = null,
        ?array $json = null,
        ?array $headers = null,
        ?int $requestsCount = null,
    ): void {
        for ($counter = 0; $counter < ($requestsCount ?? 1); ++$counter) {
            TestResponseFactory::addResponse(
                new StrictTestResponse(
                    method: $method,
                    url: $url,
                    query: $query,
                    body: $body,
                    json: $json,
                    headers: $headers,
                    responseData: new TransportException('Test exception arranged by ' . __METHOD__),
                )
            );
        }
    }
}
