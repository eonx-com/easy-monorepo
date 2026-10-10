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

    /**
     * @see testMatchesPortWhenAllowlistEntryHasOne
     */
    public static function providePortUrls(): iterable
    {
        yield 'Explicit matching port' => ['https://api.ahi.example:8443/webhooks', ['api.ahi.example:8443'], true];

        yield 'Explicit other port' => ['https://api.ahi.example:6379/webhooks', ['api.ahi.example:8443'], false];

        yield 'Implicit https port' => ['https://api.ahi.example/webhooks', ['api.ahi.example:443'], true];

        yield 'Implicit http port' => ['http://api.ahi.example/webhooks', ['api.ahi.example:80'], true];

        yield 'Implicit https port vs 80' => ['https://api.ahi.example/webhooks', ['api.ahi.example:80'], false];

        yield 'Host without port matches any port' => [
            'https://api.ahi.example:6379/webhooks',
            ['api.ahi.example'],
            true,
        ];
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

    public function testForcesMaxRedirectsToZeroForAllowedHostEvenWhenWebhookSetsIt(): void
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

    #[DataProvider('providePortUrls')]
    public function testMatchesPortWhenAllowlistEntryHasOne(string $url, array $allowedHosts, bool $expectAllowed): void
    {
        $protectedClient = new MockHttpClient(new MockResponse('protected'));
        $unprotectedClient = new MockHttpClient(new MockResponse('unprotected'));
        $client = new AllowedHostsHttpClient($protectedClient, $unprotectedClient, $allowedHosts);

        $content = $client->request('POST', $url)
            ->getContent();

        self::assertSame($expectAllowed ? 'unprotected' : 'protected', $content);
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

        $content = $client->request('POST', 'https://api.other.example/webhooks')
            ->getContent();

        self::assertSame('protected', $content);
        self::assertNull($captured['max_redirects'] ?? null);
        self::assertSame(0, $unprotectedClient->getRequestsCount());
    }

    public function testStreamsAllowedAndProtectedResponsesTogether(): void
    {
        $baseClient = new MockHttpClient([new MockResponse('first'), new MockResponse('second')]);
        $client = new AllowedHostsHttpClient(
            new NoPrivateNetworkHttpClient($baseClient),
            $baseClient,
            ['api.ahi.example']
        );

        $allowed = $client->request('GET', 'https://api.ahi.example/');
        $protected = $client->request('GET', 'https://api.other.example/', [
            'resolve' => ['api.other.example' => '8.8.8.8'],
        ]);

        $contents = [];

        foreach ($client->stream([$allowed, $protected]) as $response => $chunk) {
            if ($chunk->isLast()) {
                $contents[] = $response->getContent();
            }
        }

        self::assertSame(['first', 'second'], $contents);
    }

    public function testWithOptionsKeepsRoutingOnBothClients(): void
    {
        $protectedClient = new MockHttpClient(new MockResponse('protected'));
        $unprotectedClient = new MockHttpClient(new MockResponse('unprotected'));
        $client = (new AllowedHostsHttpClient($protectedClient, $unprotectedClient, ['api.ahi.example']))
            ->withOptions(['headers' => ['X-Test' => '1']]);

        self::assertSame('unprotected', $client->request('GET', 'https://api.ahi.example/')->getContent());
        self::assertSame('protected', $client->request('GET', 'https://api.other.example/')->getContent());
    }
}
