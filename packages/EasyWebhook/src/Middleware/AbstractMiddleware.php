<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyUtils\Traits\HasPriorityTrait;
use EonX\EasyWebhook\Interfaces\MiddlewareInterface;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

abstract class AbstractMiddleware implements MiddlewareInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }

    protected function passOn(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        return $stack
            ->next()
            ->process($webhook, $stack);
    }
}
