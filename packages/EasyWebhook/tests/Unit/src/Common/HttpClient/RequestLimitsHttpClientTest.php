<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\HttpClient;

use EonX\EasyWebhook\Common\Exception\WebhookResponseTooLargeException;
use EonX\EasyWebhook\Common\HttpClient\RequestLimitsHttpClient;
use EonX\EasyWebhook\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class RequestLimitsHttpClientTest extends AbstractUnitTestCase
{
    public function testAbortsWhenResponseExceedsLimit(): void
    {
        $client = new RequestLimitsHttpClient(
            new MockHttpClient(new MockResponse(\str_repeat('a', 1024 * 1024))),
            timeout: 0,
            maxDuration: 0,
            maxResponseBytes: 100 * 1024
        );

        try {
            $client->request('GET', 'https://example.test/big')
                ->getContent();
            self::fail('Expected the oversized response to be aborted');
        } catch (TransportExceptionInterface $exception) {
            // Symfony wraps the passthru exception in a TransportException, so the distinct marker
            // the retry layer keys off is preserved in the chain rather than at the top
            self::assertTrue(WebhookResponseTooLargeException::isInChain($exception));
        }
    }

    public function testClampsTimeLimitsThatWouldWeakenThem(): void
    {
        $captured = [];
        $client = new RequestLimitsHttpClient(
            new MockHttpClient(function (string $method, string $url, array $options) use (&$captured): MockResponse {
                $captured = $options;

                return new MockResponse('ok');
            }),
            timeout: 10,
            maxDuration: 30,
            maxResponseBytes: 0
        );

        // A webhook trying to disable/raise the guards must be clamped back to the ceiling
        $client->request('GET', 'https://example.test', [
            'timeout' => 9999,
            'max_duration' => 0,
        ])->getContent();

        self::assertSame(10.0, (float)$captured['timeout']);
        self::assertSame(30.0, (float)$captured['max_duration']);
    }

    public function testDoesNotCapResponseWhenSizeLimitIsZero(): void
    {
        $client = new RequestLimitsHttpClient(
            new MockHttpClient(new MockResponse(\str_repeat('a', 1024 * 1024))),
            timeout: 0,
            maxDuration: 0,
            maxResponseBytes: 0
        );

        $content = $client->request('GET', 'https://example.test/big')
            ->getContent();

        self::assertSame(1024 * 1024, \strlen($content));
    }

    public function testKeepsStricterPerRequestTimeLimits(): void
    {
        $captured = [];
        $client = new RequestLimitsHttpClient(
            new MockHttpClient(function (string $method, string $url, array $options) use (&$captured): MockResponse {
                $captured = $options;

                return new MockResponse('ok');
            }),
            timeout: 10,
            maxDuration: 30,
            maxResponseBytes: 0
        );

        // A stricter (smaller) per-request value is honoured
        $client->request('GET', 'https://example.test', [
            'timeout' => 3,
            'max_duration' => 5,
        ])->getContent();

        self::assertSame(3.0, (float)$captured['timeout']);
        self::assertSame(5.0, (float)$captured['max_duration']);
    }

    public function testLeavesTimeLimitsUntouchedWhenCeilingDisabled(): void
    {
        $captured = [];
        $client = new RequestLimitsHttpClient(
            new MockHttpClient(function (string $method, string $url, array $options) use (&$captured): MockResponse {
                $captured = $options;

                return new MockResponse('ok');
            }),
            timeout: 0,
            maxDuration: 0,
            maxResponseBytes: 0
        );

        $client->request('GET', 'https://example.test', [
            'timeout' => 9999,
            'max_duration' => 0,
        ])->getContent();

        self::assertSame(9999.0, (float)$captured['timeout']);
        self::assertSame(0.0, (float)$captured['max_duration']);
    }

    public function testPassesResponseUnderLimit(): void
    {
        $client = new RequestLimitsHttpClient(
            new MockHttpClient(new MockResponse(\str_repeat('b', 50 * 1024))),
            timeout: 0,
            maxDuration: 0,
            maxResponseBytes: 100 * 1024
        );

        $content = $client->request('GET', 'https://example.test/small')
            ->getContent();

        self::assertSame(50 * 1024, \strlen($content));
    }

    public function testPreservesCallerOnProgress(): void
    {
        $calls = 0;
        $client = new RequestLimitsHttpClient(
            new MockHttpClient(new MockResponse('hello')),
            timeout: 0,
            maxDuration: 0,
            maxResponseBytes: 100 * 1024
        );

        $client
            ->request('GET', 'https://example.test/x', [
                'on_progress' => static function () use (&$calls): void {
                    $calls++;
                },
            ])
            ->getContent();

        self::assertGreaterThan(0, $calls);
    }
}
