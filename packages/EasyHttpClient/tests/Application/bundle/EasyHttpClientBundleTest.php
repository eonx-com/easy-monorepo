<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Application\Bundle;

use EonX\EasyHttpClient\Common\HttpClient\WithEventsHttpClient;
use EonX\EasyHttpClient\Tests\Application\AbstractApplicationTestCase;
use EonX\EasyWebhook\Bundle\Enum\ConfigServiceId as EasyWebhookConfigServiceId;

final class EasyHttpClientBundleTest extends AbstractApplicationTestCase
{
    public function testDecorateEasyWebhookClient(): void
    {
        self::assertInstanceOf(
            WithEventsHttpClient::class,
            self::getContainer()->get(EasyWebhookConfigServiceId::HttpClient->value)
        );
    }
}
