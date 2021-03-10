<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\Stores\ResetStoreInterface;
use EonX\EasyWebhook\Interfaces\Stores\ResultStoreInterface;
use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class ResetStoreMiddleware extends AbstractMiddleware
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\Stores\ResultStoreInterface
     */
    private $resultStore;

    /**
     * @var \EonX\EasyWebhook\Interfaces\Stores\StoreInterface
     */
    private $store;

    public function __construct(StoreInterface $store, ResultStoreInterface $resultStore, ?int $priority = null)
    {
        $this->store = $store;
        $this->resultStore = $resultStore;

        parent::__construct($priority);
    }

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        foreach ([$this->store, $this->resultStore] as $store) {
            if ($store instanceof ResetStoreInterface) {
                $store->reset();
            }
        }

        return $stack
            ->next()
            ->process($webhook, $stack);
    }
}
