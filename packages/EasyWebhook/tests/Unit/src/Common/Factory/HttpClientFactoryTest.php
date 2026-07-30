<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Factory;

use EonX\EasyWebhook\Common\Factory\HttpClientFactory;
use EonX\EasyWebhook\Common\HttpClient\MaxResponseSizeHttpClient;
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
        // Opt-in: disabled by default, so no response-size cap is applied
        self::assertNotInstanceOf(MaxResponseSizeHttpClient::class, (new HttpClientFactory())->create());
    }

    public function testCreateCapsResponseSizeWhenEnabled(): void
    {
        self::assertInstanceOf(
            MaxResponseSizeHttpClient::class,
            (new HttpClientFactory(enabled: true))->create()
        );
    }

    public function testCreateDoesNotCapResponseSizeWhenLimitIsZero(): void
    {
        $httpClient = (new HttpClientFactory(enabled: true, maxResponseBytes: 0))->create();

        self::assertNotInstanceOf(MaxResponseSizeHttpClient::class, $httpClient);
    }
}
