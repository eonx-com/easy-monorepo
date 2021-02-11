<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class IdHeaderMiddleware extends AbstractConfigureOnceMiddleware
{
    /**
     * @var string
     */
    private $idHeader;

    /**
     * @var \EonX\EasyWebhook\Interfaces\Stores\StoreInterface
     */
    private $store;

    public function __construct(StoreInterface $store, ?string $idHeader = null, ?int $priority = null)
    {
        $this->store = $store;
        $this->idHeader = $idHeader ?? WebhookInterface::HEADER_ID;

        parent::__construct($priority);
    }

    protected function doProcess(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $webhook->id($webhook->getId() ?? $this->store->generateWebhookId());
        $webhook->header($this->idHeader, $webhook->getId());

        return $stack
            ->next()
            ->process($webhook, $stack);
    }
}
