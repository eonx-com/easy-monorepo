<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Bridge\Laravel;

use EonX\EasyWebhook\Interfaces\WebhookClientInterface;

final class EasyWebhookServiceProviderTest extends AbstractLumenTestCase
{
    public function testSanity(): void
    {
        $app = $this->getApplication();

        self::assertInstanceOf(WebhookClientInterface::class, $app->make(WebhookClientInterface::class));
    }
}
