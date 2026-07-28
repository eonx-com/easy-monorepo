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

    public function testCreateCapsResponseSizeByDefault(): void
    {
        self::assertInstanceOf(MaxResponseSizeHttpClient::class, (new HttpClientFactory())->create());
    }

    public function testCreateDoesNotCapResponseSizeWhenDisabled(): void
    {
        $httpClient = (new HttpClientFactory(maxResponseBytes: 0))->create();

        self::assertInstanceOf(HttpClientInterface::class, $httpClient);
        self::assertNotInstanceOf(MaxResponseSizeHttpClient::class, $httpClient);
    }
}
