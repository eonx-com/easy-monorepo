<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\Stores\ResultStoreInterface;
use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\ShouldNotBeStoredWebhookResult;

final class StoreMiddleware extends AbstractMiddleware
{
    public function __construct(
        private StoreInterface $store,
        private ResultStoreInterface $resultStore,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $webhookResult = $this->passOn($webhook, $stack);

        if ($webhookResult instanceof ShouldNotBeStoredWebhookResult) {
            return $webhookResult;
        }

        $this->store->store($webhook);

        return $this->resultStore->store($webhookResult);
    }
}
