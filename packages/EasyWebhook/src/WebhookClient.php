<?php

declare(strict_types=1);

namespace EonX\EasyWebhook;

use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class WebhookClient implements WebhookClientInterface
{
    public function __construct(
        private StackInterface $stack,
    ) {
    }

    public function getStack(): StackInterface
    {
        return $this->stack;
    }

    public function sendWebhook(WebhookInterface $webhook): WebhookResultInterface
    {
        // Make sure stack is "fresh"
        $this->stack->rewind();

        return $this->stack
            ->next()
            ->process($webhook, $this->stack);
    }
}
