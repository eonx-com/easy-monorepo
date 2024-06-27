<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Client;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Stack\StackInterface;

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
