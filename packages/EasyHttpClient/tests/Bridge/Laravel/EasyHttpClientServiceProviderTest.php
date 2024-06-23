<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Bridge\Laravel;

use EonX\EasyEventDispatcher\Bridge\Laravel\EasyEventDispatcherServiceProvider;
use EonX\EasyHttpClient\Implementations\Symfony\WithEventsHttpClient;
use EonX\EasyWebhook\Bundle\Enum\ConfigServiceId as EasyWebhookConfigServiceId;
use EonX\EasyWebhook\Laravel\EasyWebhookServiceProvider;

final class EasyHttpClientServiceProviderTest extends AbstractLaravelTestCase
{
    public function testDecorateEasyWebhookClient(): void
    {
        $app = $this->getApp([
            'easy-http-client' => [
                'decorate_easy_webhook_client' => true,
            ],
        ], [
            EasyEventDispatcherServiceProvider::class,
            EasyWebhookServiceProvider::class,
        ]);

        self::assertInstanceOf(
            WithEventsHttpClient::class,
            $app->get(EasyWebhookConfigServiceId::HttpClient->value)
        );
    }
}
