<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use EonX\EasyUtils\Common\Helper\HasPriorityTrait;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Stack\StackInterface;

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
