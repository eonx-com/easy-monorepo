<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stub\Middleware;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResult;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Middleware\AbstractMiddleware;
use EonX\EasyWebhook\Common\Stack\StackInterface;
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
