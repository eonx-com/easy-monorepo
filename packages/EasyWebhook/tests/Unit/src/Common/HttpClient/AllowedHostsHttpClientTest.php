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

        yield 'Upper case with default port' => ['https://API.AHI.EXAMPLE:443/webhooks'];
    }

    /**
     * @see testIpLiteralNeverMatchesAllowlist
     */
    public static function provideIpLiteralUrls(): iterable
    {
        yield 'IPv4' => ['http://10.24.80.5/webhooks'];

        yield 'IPv6' => ['http://[fd00::1]/webhooks'];

        yield 'Short IPv4' => ['http://127.1/webhooks'];

        yield 'Decimal IPv4' => ['http://2130706433/webhooks'];

        yield 'Hex IPv4' => ['http://0x7f000001/webhooks'];

        yield 'IPv4 with trailing dot' => ['http://127.0.0.1./webhooks'];

        yield 'Short IPv4 with trailing dot' => ['http://127.1./webhooks'];
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

        yield 'Host without port matches default https port' => [
            'https://api.ahi.example/webhooks',
            ['api.ahi.example'],
            true,
        ];

        yield 'Host without port matches default http port' => [
            'http://api.ahi.example/webhooks',
            ['api.ahi.example'],
            true,
        ];

        yield 'Host without port does not match other port' => [
            'http://api.ahi.example:6379/webhooks',
            ['api.ahi.example'],
            false,
        ];

        yield 'Host without port does not match https on port 80' => [
            'https://api.ahi.example:80/webhooks',
            ['api.ahi.example'],
            false,
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
        $client = new AllowedHostsHttpClient(
            $protectedClient,
            $unprotectedClient,
            ['10.24.80.5', 'fd00::1', '127.1', '2130706433', '0x7f000001', '127.0.0.1.', '127.1.']
        );

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
            $contents[\spl_object_id($response)] = ($contents[\spl_object_id($response)] ?? '') . $chunk->getContent();
        }

        self::assertSame(
            ['first', 'second'],
            [$contents[\spl_object_id($allowed)], $contents[\spl_object_id($protected)]]
        );
    }

    public function testStreamsEachPathThroughItsOwnClient(): void
    {
        $protectedBaseClient = new MockHttpClient([new MockResponse('protected-1'), new MockResponse('protected-2')]);
        $unprotectedClient = new MockHttpClient([new MockResponse('unprotected')]);
        $client = new AllowedHostsHttpClient(
            new NoPrivateNetworkHttpClient($protectedBaseClient),
            $unprotectedClient,
            ['api.ahi.example']
        );
        $options = [
            'resolve' => ['api.other.example' => '8.8.8.8'],
        ];

        $protectedResponses = [
            $client->request('GET', 'https://api.other.example/1', $options),
            $client->request('GET', 'https://api.other.example/2', $options),
        ];
        $protectedContent = '';

        foreach ($client->stream($protectedResponses) as $chunk) {
            $protectedContent .= $chunk->getContent();
        }

        $allowedContent = '';

        foreach ($client->stream($client->request('GET', 'https://api.ahi.example/')) as $chunk) {
            $allowedContent .= $chunk->getContent();
        }

        self::assertSame('protected-1protected-2', $protectedContent);
        self::assertSame('unprotected', $allowedContent);
        self::assertSame(2, $protectedBaseClient->getRequestsCount());
        self::assertSame(1, $unprotectedClient->getRequestsCount());
    }

    public function testWithOptionsCannotStreamAllowedAndProtectedResponsesTogether(): void
    {
        $baseClient = new MockHttpClient(static fn(): MockResponse => new MockResponse('ok'));
        $client = new AllowedHostsHttpClient(
            new NoPrivateNetworkHttpClient($baseClient),
            $baseClient,
            ['api.ahi.example']
        )->withOptions(['headers' => ['X-Test' => '1']]);
        $responses = [
            $client->request('GET', 'https://api.ahi.example/'),
            $client->request('GET', 'https://api.other.example/', [
                'resolve' => ['api.other.example' => '8.8.8.8'],
            ]),
        ];

        $this->expectException(TransportExceptionInterface::class);
        $this->expectExceptionMessage('Cannot stream AsyncResponse objects with many clients.');

        foreach ($client->stream($responses) as $chunk) {
            $chunk->getContent();
        }
    }

    public function testWithOptionsKeepsRoutingOnBothClients(): void
    {
        $protectedClient = new MockHttpClient(new MockResponse('protected'));
        $unprotectedClient = new MockHttpClient(new MockResponse('unprotected'));
        $client = new AllowedHostsHttpClient($protectedClient, $unprotectedClient, ['api.ahi.example'])
            ->withOptions(['headers' => ['X-Test' => '1']]);

        self::assertSame('unprotected', $client->request('GET', 'https://api.ahi.example/')->getContent());
        self::assertSame('protected', $client->request('GET', 'https://api.other.example/')->getContent());
    }
}
