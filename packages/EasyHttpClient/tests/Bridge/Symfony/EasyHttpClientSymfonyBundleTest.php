<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Bridge\Symfony;

use EonX\EasyHttpClient\Bridge\BridgeConstantsInterface;
use EonX\EasyHttpClient\Implementations\Symfony\WithEventsHttpClient;
use EonX\EasyWebhook\Bridge\BridgeConstantsInterface as EasyWebhookBridgeConstantsInterface;

final class EasyHttpClientSymfonyBundleTest extends AbstractSymfonyTestCase
{
    public function testDecorateDefaultClient(): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/decorate_default_client.yaml'])->getContainer();

        self::assertInstanceOf(WithEventsHttpClient::class, $container->get('http_client'));
    }

    public function testDecorateEasyWebhookClient(): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/decorate_easy_webhook_client.yaml'])->getContainer();

        self::assertInstanceOf(
            WithEventsHttpClient::class,
            $container->get(EasyWebhookBridgeConstantsInterface::HTTP_CLIENT)
        );
    }

    public function testSanity(): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/decorate_default_client.yaml'])->getContainer();

        self::assertInstanceOf(
            WithEventsHttpClient::class,
            $container->get(BridgeConstantsInterface::SERVICE_HTTP_CLIENT)
        );
    }
}
