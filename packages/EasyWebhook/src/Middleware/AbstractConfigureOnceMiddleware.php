<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

abstract class AbstractConfigureOnceMiddleware extends AbstractMiddleware
{
    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        if ($webhook->isConfigured()) {
            return $stack
                ->next()
                ->process($webhook, $stack);
        }

        return $this->doProcess($webhook, $stack);
    }

    abstract protected function doProcess(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface;
}
