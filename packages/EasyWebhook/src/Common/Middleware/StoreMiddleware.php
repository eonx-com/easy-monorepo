<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\ShouldNotBeStoredWebhookResult;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Stack\StackInterface;
use EonX\EasyWebhook\Common\Store\ResultStoreInterface;
use EonX\EasyWebhook\Common\Store\StoreInterface;

final class StoreMiddleware extends AbstractMiddleware
{
    public function __construct(
        private readonly StoreInterface $store,
        private readonly ResultStoreInterface $resultStore,
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
