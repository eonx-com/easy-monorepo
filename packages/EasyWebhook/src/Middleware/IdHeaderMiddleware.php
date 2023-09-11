<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

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
