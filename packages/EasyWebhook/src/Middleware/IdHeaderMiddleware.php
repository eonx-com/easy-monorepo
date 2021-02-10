<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyWebhook\Interfaces\IdAwareWebhookResultStoreInterface;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;

final class IdHeaderMiddleware extends AbstractConfigureOnceMiddleware
{
    /**
     * @var string
     */
    private $idHeader;

    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface
     */
    private $store;

    public function __construct(WebhookResultStoreInterface $store, ?string $idHeader = null, ?int $priority = null)
    {
        $this->store = $store;
        $this->idHeader = $idHeader ?? WebhookInterface::HEADER_ID;

        parent::__construct($priority);
    }

    protected function doProcess(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $webhookId = $this->getWebhookId($webhook);

        if ($webhookId !== null) {
            $webhook->id($webhookId);
            $webhook->header($this->idHeader, $webhook->getId());
        }

        return $stack
            ->next()
            ->process($webhook, $stack);
    }

    private function getWebhookId(WebhookInterface $webhook): ?string
    {
        if ($webhook->getId() !== null) {
            return $webhook->getId();
        }

        if ($this->store instanceof IdAwareWebhookResultStoreInterface) {
            return $this->store->generateWebhookId();
        }

        return null;
    }
}
