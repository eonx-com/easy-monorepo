<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Configurators;

use EonX\EasyWebhook\Interfaces\IdAwareWebhookResultStoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;

final class IdWebhookConfigurator extends AbstractWebhookConfigurator
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
        $this->idHeader = $idHeader ?? self::HEADER_ID;

        parent::__construct($priority);
    }

    public function configure(WebhookInterface $webhook): void
    {
        $webhookId = $this->getWebhookId($webhook);

        if ($webhookId === null) {
            return;
        }

        $webhook->id($webhookId);

        $webhook->mergeHttpClientOptions([
            'headers' => [
                $this->idHeader => $webhook->getId(),
            ],
        ]);
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
