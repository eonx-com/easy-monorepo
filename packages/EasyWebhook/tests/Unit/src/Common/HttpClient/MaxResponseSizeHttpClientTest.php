<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\HttpClient;

use EonX\EasyWebhook\Common\HttpClient\MaxResponseSizeHttpClient;
use EonX\EasyWebhook\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class MaxResponseSizeHttpClientTest extends AbstractUnitTestCase
{
    public function testAbortsWhenResponseExceedsLimit(): void
    {
        $client = new MaxResponseSizeHttpClient(
            new MockHttpClient(new MockResponse(\str_repeat('a', 1024 * 1024))),
            100 * 1024
        );

        $this->expectException(TransportExceptionInterface::class);

        $client->request('GET', 'https://example.test/big')
            ->getContent();
    }

    public function testPassesResponseUnderLimit(): void
    {
        $client = new MaxResponseSizeHttpClient(
            new MockHttpClient(new MockResponse(\str_repeat('b', 50 * 1024))),
            100 * 1024
        );

        $content = $client->request('GET', 'https://example.test/small')
            ->getContent();

        self::assertSame(50 * 1024, \strlen($content));
    }

    public function testPreservesCallerOnProgress(): void
    {
        $calls = 0;
        $client = new MaxResponseSizeHttpClient(
            new MockHttpClient(new MockResponse('hello')),
            100 * 1024
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
