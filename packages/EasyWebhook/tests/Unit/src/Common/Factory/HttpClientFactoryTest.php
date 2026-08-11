<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Factory;

use EonX\EasyWebhook\Common\Exception\InvalidSsrfProtectionConfigException;
use EonX\EasyWebhook\Common\Factory\HttpClientFactory;
use EonX\EasyWebhook\Common\HttpClient\RequestLimitsHttpClient;
use EonX\EasyWebhook\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class HttpClientFactoryTest extends AbstractUnitTestCase
{
    /**
     * allowed_ranges must carve out only the range it removes: after allowing IPv4 localhost, a
     * loopback request passes the SSRF gate while the cloud metadata endpoint stays blocked. The
     * gate runs synchronously in request(), which is otherwise lazy, so no network call is made
     */
    public function testAllowedRangesCarveOutMatchingDefaultButKeepOthersBlocked(): void
    {
        $httpClient = (new HttpClientFactory(allowedRanges: ['127.0.0.0/8']))->create();

        $response = $httpClient->request('GET', 'http://127.0.0.1/');
        $response->cancel();

        self::assertInstanceOf(ResponseInterface::class, $response);
        $this->assertRequestBlocked($httpClient, 'http://169.254.169.254/latest/meta-data/');
    }

    public function testCreate(): void
    {
        self::assertInstanceOf(HttpClientInterface::class, (new HttpClientFactory())->create());
    }

    public function testCreateAppliesNoRequestLimitsWhenDisabledByDefault(): void
    {
        // Request limits are opt-in, so by default the client is not wrapped by RequestLimitsHttpClient
        self::assertNotInstanceOf(RequestLimitsHttpClient::class, (new HttpClientFactory())->create());
    }

    public function testCreateBlocksExtraRanges(): void
    {
        self::assertInstanceOf(
            NoPrivateNetworkHttpClient::class,
            (new HttpClientFactory(extraBlockedRanges: ['8.8.8.8/32']))->create()
        );
    }

    public function testCreateBlocksPrivateNetworksByDefault(): void
    {
        self::assertInstanceOf(NoPrivateNetworkHttpClient::class, (new HttpClientFactory())->create());
    }

    public function testCreateEnforcesRequestLimitsWhenEnabled(): void
    {
        self::assertInstanceOf(
            RequestLimitsHttpClient::class,
            (new HttpClientFactory(requestLimitsEnabled: true))->create()
        );
    }

    public function testCreateReturnsPlainClientWhenProtectionDisabled(): void
    {
        $httpClient = (new HttpClientFactory(blockPrivateNetworks: false))->create();

        self::assertInstanceOf(HttpClientInterface::class, $httpClient);
        self::assertNotInstanceOf(NoPrivateNetworkHttpClient::class, $httpClient);
    }

    public function testCreateWithAllowedRanges(): void
    {
        self::assertInstanceOf(
            NoPrivateNetworkHttpClient::class,
            (new HttpClientFactory(allowedRanges: ['127.0.0.0/8']))->create()
        );
    }

    public function testDoesNotValidateAllowedRangesWhenProtectionDisabled(): void
    {
        // Protection off -> allowed_ranges is inert, so an otherwise-invalid entry must not throw
        $httpClient = (new HttpClientFactory(blockPrivateNetworks: false, allowedRanges: ['8.8.8.8/32']))
            ->create();

        self::assertNotInstanceOf(NoPrivateNetworkHttpClient::class, $httpClient);
    }

    /**
     * extra_blocked_ranges must be added to the defaults, not replace them: the extra range and
     * the standard private/reserved ranges are both blocked
     */
    public function testExtraBlockedRangesAddToDefaultsWithoutReplacing(): void
    {
        $httpClient = (new HttpClientFactory(extraBlockedRanges: ['8.8.8.8/32']))->create();

        $this->assertRequestBlocked($httpClient, 'http://8.8.8.8/');
        $this->assertRequestBlocked($httpClient, 'http://169.254.169.254/latest/meta-data/');
    }

    public function testRejectsAllowedRangeCoveredByBroaderRange(): void
    {
        // Build the overlap from our own ranges so it does not depend on Symfony's version-specific
        // defaults (e.g. ::/96 only exists from http-foundation 7.4.13). Removing 203.0.113.5/32
        // leaves it covered by the broader 203.0.113.0/24, so the address stays blocked; this must
        // fail loudly, not silently do nothing
        $this->expectException(InvalidSsrfProtectionConfigException::class);

        new HttpClientFactory(
            extraBlockedRanges: ['203.0.113.0/24', '203.0.113.5/32'],
            allowedRanges: ['203.0.113.5/32']
        );
    }

    public function testRejectsAllowedRangeThatIsNotADefault(): void
    {
        // A non-default range (typo, non-canonical CIDR, or public IP) would carve out nothing
        $this->expectException(InvalidSsrfProtectionConfigException::class);

        new HttpClientFactory(allowedRanges: ['8.8.8.8/32']);
    }

    /**
     * A request whose resolved IP falls in a blocked range is rejected up-front, before any
     * connection is attempted, so this is deterministic and needs no network. The link-local
     * 169.254.0.0/16 range covers the cloud metadata endpoint that SSRF payloads target
     */
    public function testRequestToCloudMetadataEndpointIsBlocked(): void
    {
        $httpClient = (new HttpClientFactory())->create();

        $this->expectException(TransportExceptionInterface::class);

        $httpClient->request('GET', 'http://169.254.169.254/latest/meta-data/');
    }

    /**
     * DNS-rebinding: a public hostname pinned (via the resolve option, so no real lookup) to the
     * cloud metadata IP. This is the case a URL-string check cannot catch and is what this guard
     * really buys — the block is enforced on the RESOLVED IP, which NoPrivateNetworkHttpClient
     * inspects before any connection
     */
    public function testRequestToPublicHostResolvingToPrivateIpIsBlocked(): void
    {
        $httpClient = (new HttpClientFactory())->create();

        $this->expectException(TransportExceptionInterface::class);

        $httpClient->request('GET', 'http://evil.example.com/', [
            'resolve' => ['evil.example.com' => '169.254.169.254'],
        ]);
    }

    private function assertRequestBlocked(HttpClientInterface $httpClient, string $url): void
    {
        $blocked = false;

        try {
            $httpClient->request('GET', $url);
        } catch (TransportExceptionInterface) {
            $blocked = true;
        }

        self::assertTrue($blocked, "Expected request to {$url} to be blocked");
    }
}
