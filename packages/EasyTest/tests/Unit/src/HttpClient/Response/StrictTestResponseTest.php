<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests\Unit\HttpClient\Response;

use EonX\EasyTest\HttpClient\Response\StrictTestResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\MockResponse;

final class StrictTestResponseTest extends TestCase
{
    public function testItSucceedsWithUnsortedRequestData(): void
    {
        $sut = new StrictTestResponse(
            method: 'GET',
            url: 'https://example.com',
            json: [
                'foo' => 'bar',
                'bar' => 'baz',
                'tags' => ['foo', 'bar'],
            ],
            headers: [
                'X-Request-Id' => '123',
                'Content-Type' => 'application/json',
            ],
            responseData: [
                'some' => 'data',
            ],
            responseCode: 200,
        );

        $mockResponse = $sut(
            method: 'GET',
            url: 'https://example.com',
            options: [
                'normalized_headers' => [
                    'content-type' => [
                        'Content-Type: application/json',
                    ],
                    'x-request-id' => [
                        'X-Request-Id: 123',
                    ],
                ],
                'body' => \json_encode([
                    'bar' => 'baz',
                    'foo' => 'bar',
                    'tags' => ['foo', 'bar'],
                ]),
            ]
        );

        self::assertInstanceOf(MockResponse::class, $mockResponse);
        self::assertSame(200, $mockResponse->getInfo()['http_code']);
    }
}
