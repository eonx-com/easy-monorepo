<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\HttpClient;

use EonX\EasyWebhook\Common\HttpClient\AllowedHostsHttpClient;
use EonX\EasyWebhook\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class AllowedHostsHttpClientTest extends AbstractUnitTestCase
{
    /**
     * @see testAllowedHostGoesToUnprotectedClientRegardlessOfCase
     */
    public static function provideAllowedHostUrls(): iterable
    {
        yield 'Lower case' => ['https://api.ahi.example/webhooks'];

        yield 'Mixed case' => ['https://Api.AHI.Example/webhooks'];

        yield 'Upper case with port' => ['https://API.AHI.EXAMPLE:8443/webhooks'];
    }

    /**
     * @see testIpLiteralNeverMatchesAllowlist
     */
    public static function provideIpLiteralUrls(): iterable
    {
        yield 'IPv4' => ['http://10.24.80.5/webhooks'];

        yield 'IPv6' => ['http://[fd00::1]/webhooks'];
    }

    #[DataProvider('provideAllowedHostUrls')]
    public function testAllowedHostGoesToUnprotectedClientRegardlessOfCase(string $url): void
    {
        $protectedClient = new MockHttpClient(new MockResponse('protected'));
        $unprotectedClient = new MockHttpClient(new MockResponse('unprotected'));
        $client = new AllowedHostsHttpClient($protectedClient, $unprotectedClient, ['api.ahi.example']);

        $content = $client->request('POST', $url)
            ->getContent();

        self::assertSame('unprotected', $content);
        self::assertSame(1, $unprotectedClient->getRequestsCount());
        self::assertSame(0, $protectedClient->getRequestsCount());
    }

    public function testDisablesRedirectsForAllowedHostByDefault(): void
    {
        $captured = [];
        $unprotectedClient = new MockHttpClient(
            static function (string $method, string $url, array $options) use (&$captured): MockResponse {
                $captured = $options;

                return new MockResponse('ok');
            }
        );
        $client = new AllowedHostsHttpClient(new MockHttpClient(), $unprotectedClient, ['api.ahi.example']);

        $client->request('POST', 'https://api.ahi.example/webhooks')
            ->getContent();

        self::assertSame(0, $captured['max_redirects']);
    }

    public function testHostNotInAllowlistResolvingToPrivateIpIsBlocked(): void
    {
        $client = new AllowedHostsHttpClient(
            new NoPrivateNetworkHttpClient(new MockHttpClient(new MockResponse('ok'))),
            new MockHttpClient(new MockResponse('unprotected')),
            ['api.ahi.example']
        );

        $this->expectException(TransportExceptionInterface::class);
        $this->expectExceptionMessage('Host "evil.example.com" is blocked for "http://evil.example.com/".');

        $client->request('GET', 'http://evil.example.com/', [
            'resolve' => ['evil.example.com' => '10.24.80.5'],
        ]);
    }

    #[DataProvider('provideIpLiteralUrls')]
    public function testIpLiteralNeverMatchesAllowlist(string $url): void
    {
        $protectedClient = new MockHttpClient(new MockResponse('protected'));
        $unprotectedClient = new MockHttpClient(new MockResponse('unprotected'));
        $client = new AllowedHostsHttpClient($protectedClient, $unprotectedClient, ['10.24.80.5', 'fd00::1']);

        $content = $client->request('GET', $url)
            ->getContent();

        self::assertSame('protected', $content);
        self::assertSame(1, $protectedClient->getRequestsCount());
        self::assertSame(0, $unprotectedClient->getRequestsCount());
    }

    public function testKeepsWebhookMaxRedirectsForAllowedHost(): void
    {
        $captured = [];
        $unprotectedClient = new MockHttpClient(
            static function (string $method, string $url, array $options) use (&$captured): MockResponse {
                $captured = $options;

                return new MockResponse('ok');
            }
        );
        $client = new AllowedHostsHttpClient(new MockHttpClient(), $unprotectedClient, ['api.ahi.example']);

        $client->request('POST', 'https://api.ahi.example/webhooks', ['max_redirects' => 3])
            ->getContent();

        self::assertSame(3, $captured['max_redirects']);
    }

    public function testNonAllowedHostGoesToProtectedClientWithOptionsUntouched(): void
    {
        $captured = [];
        $protectedClient = new MockHttpClient(
            static function (string $method, string $url, array $options) use (&$captured): MockResponse {
                $captured = $options;

                return new MockResponse('protected');
            }
        );
        $unprotectedClient = new MockHttpClient(new MockResponse('unprotected'));
        $client = new AllowedHostsHttpClient($protectedClient, $unprotectedClient, ['api.ahi.example']);

        $content = $client->request('POST', 'https://api.other.example/webhooks', [
            'resolve' => ['api.other.example' => '203.0.113.10'],
        ])->getContent();

        self::assertSame('protected', $content);
        self::assertNull($captured['max_redirects'] ?? null);
        self::assertSame(0, $unprotectedClient->getRequestsCount());
    }

    public function testPinsResolvedIpForProtectedClient(): void
    {
        $captured = [];
        $protectedClient = new MockHttpClient(
            static function (string $method, string $url, array $options) use (&$captured): MockResponse {
                $captured = $options;

                return new MockResponse('ok');
            }
        );
        $client = new AllowedHostsHttpClient($protectedClient, new MockHttpClient(), ['api.ahi.example']);

        $client->request('GET', 'http://localhost/webhooks')
            ->getContent();

        self::assertSame('127.0.0.1', $captured['resolve']['localhost'] ?? null);
    }

    public function testUnresolvableHostFailsWithResolutionMessageNotBlockedMessage(): void
    {
        $client = new AllowedHostsHttpClient(
            new NoPrivateNetworkHttpClient(new MockHttpClient(new MockResponse('ok'))),
            new MockHttpClient(new MockResponse('unprotected')),
            ['api.ahi.example']
        );

        $this->expectException(TransportExceptionInterface::class);
        $this->expectExceptionMessage(
            'Host "nonexistent.invalid" could not be resolved for "http://nonexistent.invalid/webhooks".'
        );

        $client->request('POST', 'http://nonexistent.invalid/webhooks');
    }

    public function testWithOptionsKeepsRoutingOnBothClients(): void
    {
        $protectedClient = new MockHttpClient(new MockResponse('protected'));
        $unprotectedClient = new MockHttpClient(new MockResponse('unprotected'));
        $client = (new AllowedHostsHttpClient($protectedClient, $unprotectedClient, ['api.ahi.example']))
            ->withOptions(['headers' => ['X-Test' => '1']]);

        self::assertSame('unprotected', $client->request('GET', 'https://api.ahi.example/')->getContent());
        self::assertSame('protected', $client->request('GET', 'https://api.other.example/', [
            'resolve' => ['api.other.example' => '203.0.113.10'],
        ])->getContent());
    }
}
