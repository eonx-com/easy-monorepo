<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Stack\StackInterface;

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
