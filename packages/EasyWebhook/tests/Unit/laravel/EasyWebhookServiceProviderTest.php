<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Laravel;

use EonX\EasyWebhook\Bundle\Enum\ConfigServiceId;
use EonX\EasyWebhook\Common\Client\WebhookClientInterface;
use EonX\EasyWebhook\Common\HttpClient\AllowedHostsHttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class EasyWebhookServiceProviderTest extends AbstractLumenTestCase
{
    public function testSanity(): void
    {
        $app = $this->getApplication();

        self::assertInstanceOf(WebhookClientInterface::class, $app->make(WebhookClientInterface::class));
    }

    public function testSsrfAllowedHostsAreWiredIntoHttpClient(): void
    {
        $app = $this->getApplication(null, [
            'easy-webhook.ssrf_protection.allowed_hosts' => [' Api.AHI.UAT.Example ', ''],
        ]);

        $httpClient = $app->make(ConfigServiceId::HttpClient->value);
        self::assertInstanceOf(AllowedHostsHttpClient::class, $httpClient);

        $response = $httpClient->request('GET', 'https://api.ahi.uat.example/webhooks', [
            'resolve' => ['api.ahi.uat.example' => '10.24.80.5'],
        ]);
        $response->cancel();
        self::assertInstanceOf(ResponseInterface::class, $response);

        $this->expectException(TransportExceptionInterface::class);

        $httpClient->request('GET', 'https://other.example/webhooks', [
            'resolve' => ['other.example' => '10.24.80.5'],
        ]);
    }
}
