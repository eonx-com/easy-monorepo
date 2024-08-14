<?php
declare(strict_types=1);

namespace EonX\EasyTest\HttpClient\Trait;

use EonX\EasyTest\HttpClient\Factory\TestResponseFactory;
use EonX\EasyTest\HttpClient\Response\SimpleTestResponse;
use Symfony\Component\HttpClient\Exception\TransportException;

trait HttpClientApplicationTestTrait
{
    use HttpClientCommonTestTrait;

    /**
     * @param array|string|null $responseData If null, empty array will be used for JSON or empty string otherwise
     * @param array<string, string>|null $responseHeaders
     * @param int|null $responseCode If null, 200 will be used
     */
    protected static function arrangeHttpResponse(
        string $url,
        ?array $query = null,
        array|string|null $responseData = null,
        ?array $responseHeaders = null,
        ?int $responseCode = null,
        ?int $requestsCount = null,
    ): void {
        for ($counter = 0; $counter < ($requestsCount ?? 1); ++$counter) {
            TestResponseFactory::addResponse(
                new SimpleTestResponse(
                    url: $url,
                    query: $query,
                    responseData: $responseData,
                    responseHeaders: $responseHeaders,
                    responseCode: $responseCode,
                )
            );
        }
    }

    protected static function arrangeHttpResponseWithTransportException(
        string $url,
        ?array $query = null,
        ?int $requestsCount = null,
    ): void {
        for ($counter = 0; $counter < ($requestsCount ?? 1); ++$counter) {
            TestResponseFactory::addResponse(
                new SimpleTestResponse(
                    url: $url,
                    query: $query,
                    responseData: new TransportException('Test exception arranged by ' . __METHOD__),
                )
            );
        }
    }
}
