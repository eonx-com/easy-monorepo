<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyWebhook\Interfaces\ResettableWebhookResultStoreInterface;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;

final class ResetStoreMiddleware extends AbstractMiddleware
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface
     */
    private $store;

    public function __construct(WebhookResultStoreInterface $store, ?int $priority = null)
    {
        $this->store = $store;

        parent::__construct($priority);
    }

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        if ($this->store instanceof ResettableWebhookResultStoreInterface) {
            $this->store->reset();
        }

        return $stack
            ->next()
            ->process($webhook, $stack);
    }
}
