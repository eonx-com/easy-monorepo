<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Bridge\Symfony;

use EonX\EasyHttpClient\Implementations\Symfony\WithEventsHttpClient;
use EonX\EasyHttpClient\Tests\AbstractSymfonyTestCase;
use EonX\EasyWebhook\Bridge\BridgeConstantsInterface as EasyWebhookBridgeConstantsInterface;

final class EasyHttpClientSymfonyBundleTest extends AbstractSymfonyTestCase
{
    public function testDecorateEasyWebhookClient(): void
    {
        self::assertInstanceOf(
            WithEventsHttpClient::class,
            self::getContainer()->get(EasyWebhookBridgeConstantsInterface::HTTP_CLIENT)
        );
    }
}
