<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Laravel;

use EonX\EasyWebhook\Common\Client\WebhookClientInterface;

final class EasyWebhookServiceProviderTest extends AbstractLumenTestCase
{
    public function testSanity(): void
    {
        $app = $this->getApplication();

        self::assertInstanceOf(WebhookClientInterface::class, $app->make(WebhookClientInterface::class));
    }
}
