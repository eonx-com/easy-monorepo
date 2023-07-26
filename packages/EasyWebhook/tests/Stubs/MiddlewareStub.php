<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stubs;

use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Middleware\AbstractMiddleware;
use EonX\EasyWebhook\WebhookResult;
use Throwable;

final class MiddlewareStub extends AbstractMiddleware
{
    public function __construct(
        private ?WebhookResultInterface $webhookResult = null,
        private ?Throwable $throwable = null,
    ) {
        parent::__construct();
    }

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        return $this->webhookResult ?? new WebhookResult($webhook, null, $this->throwable);
    }
}
