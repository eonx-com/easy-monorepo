<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Stack\StackInterface;
use EonX\EasyWebhook\Common\Store\ResetStoreInterface;
use EonX\EasyWebhook\Common\Store\ResultStoreInterface;
use EonX\EasyWebhook\Common\Store\StoreInterface;

final class ResetStoreMiddleware extends AbstractMiddleware
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
        foreach ([$this->store, $this->resultStore] as $store) {
            if ($store instanceof ResetStoreInterface) {
                $store->reset();
            }
        }

        return $this->passOn($webhook, $stack);
    }
}
