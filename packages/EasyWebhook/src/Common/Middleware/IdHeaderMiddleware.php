<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Stack\StackInterface;
use EonX\EasyWebhook\Common\Store\StoreInterface;

final class IdHeaderMiddleware extends AbstractConfigureOnceMiddleware
{
    private string $idHeader;

    public function __construct(
        private StoreInterface $store,
        ?string $idHeader = null,
        ?int $priority = null,
    ) {
        $this->idHeader = $idHeader ?? WebhookInterface::HEADER_ID;

        parent::__construct($priority);
    }

    protected function doProcess(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $webhook->id($webhook->getId() ?? $this->store->generateWebhookId());

        // Set header only if id isn't the default one
        if ($webhook->getId() !== StoreInterface::DEFAULT_WEBHOOK_ID) {
            $webhook->header($this->idHeader, $webhook->getId());
        }

        return $stack
            ->next()
            ->process($webhook, $stack);
    }
}
