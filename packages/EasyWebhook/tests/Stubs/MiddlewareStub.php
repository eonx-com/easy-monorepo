<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stubs;

use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Middleware\AbstractMiddleware;
use EonX\EasyWebhook\WebhookResult;

final class MiddlewareStub extends AbstractMiddleware
{
    /**
     * @var null|\EonX\EasyWebhook\Interfaces\WebhookResultInterface
     */
    private $webhookResult;

    public function __construct(?WebhookResultInterface $webhookResult = null)
    {
        $this->webhookResult = $webhookResult;

        parent::__construct();
    }

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        return $this->webhookResult ?? new WebhookResult($webhook);
    }
}
