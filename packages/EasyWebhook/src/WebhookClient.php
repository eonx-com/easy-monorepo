<?php

declare(strict_types=1);

namespace EonX\EasyWebhook;

use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class WebhookClient implements WebhookClientInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\StackInterface
     */
    private $stack;

    public function __construct(StackInterface $stack)
    {
        $this->stack = $stack;
    }

    public function getStack(): StackInterface
    {
        return $this->stack;
    }

    public function sendWebhook(WebhookInterface $webhook): WebhookResultInterface
    {
        return $this->stack
            ->next()
            ->process($webhook, $this->stack);
    }
}
