<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\HttpClient\Symfony;

use EonX\EasyCore\HttpClient\Symfony\WithLoggingHttpClient;
use EonX\EasyCore\Tests\AbstractTestCase;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class WithLoggingHttpClientTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestRequest(): iterable
    {
        yield 'simple_request' => [
            'POST',
            'https://eonx.com',
            new MockResponse((string)\json_encode([
                'message' => 'ok',
            ])),
            static function (string $logs): void {
                self::assertStringContainsString('Request: "POST https://eonx.com" {"http_options":[]} []', $logs);
                self::assertStringContainsString('Response: "200 https://eonx.com"', $logs);
            },
        ];
    }

    /**
     * @param null|mixed[] $options
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     *
     * @dataProvider providerTestRequest
     */
    public function testRequest(
        string $method,
        string $url,
        MockResponse $mockResponse,
        callable $test,
        ?array $options = null
    ): void {
        $filename = __DIR__ . '/http_client_test_stream.txt';
        $stream = \fopen($filename, 'w+');

        if ($stream === false) {
            return;
        }

        $withLogging = new WithLoggingHttpClient(
            new MockHttpClient([$mockResponse]),
            new Logger('http_client', [new StreamHandler($stream)])
        );

        $withLogging->request($method, $url, $options);

        \fclose($stream);
        $logs = \file_get_contents($filename);
        \unlink($filename);

        $test($logs);
    }
}
