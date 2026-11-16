<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stub\Client;

use EonX\EasyWebhook\Common\Client\WebhookClientInterface;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResult;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use Throwable;

final readonly class WebhookClientStub implements WebhookClientInterface
{
    public function __construct(
        private ?Throwable $throwable = null,
    ) {}

    public function sendWebhook(WebhookInterface $webhook): WebhookResultInterface
    {
        return new WebhookResult($webhook, null, $this->throwable);
    }
}
