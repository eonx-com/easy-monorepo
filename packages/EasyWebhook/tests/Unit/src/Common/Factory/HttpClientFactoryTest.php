<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Factory;

use EonX\EasyWebhook\Common\Factory\HttpClientFactory;
use EonX\EasyWebhook\Common\HttpClient\RequestLimitsHttpClient;
use EonX\EasyWebhook\Tests\Unit\AbstractUnitTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpClientFactoryTest extends AbstractUnitTestCase
{
    public function testCreate(): void
    {
        self::assertInstanceOf(HttpClientInterface::class, (new HttpClientFactory())->create());
    }

    public function testCreateAppliesNoLimitsWhenDisabledByDefault(): void
    {
        // Opt-in: disabled by default, so the request limits are not enforced at all
        self::assertNotInstanceOf(RequestLimitsHttpClient::class, (new HttpClientFactory())->create());
    }

    public function testCreateEnforcesRequestLimitsWhenEnabled(): void
    {
        self::assertInstanceOf(
            RequestLimitsHttpClient::class,
            (new HttpClientFactory(requestLimitsEnabled: true))->create()
        );
    }
}
