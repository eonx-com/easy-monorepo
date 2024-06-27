<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Store;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;

final class NullStore implements StoreInterface
{
    public function find(string $id): ?WebhookInterface
    {
        return null;
    }

    public function generateWebhookId(): string
    {
        return self::DEFAULT_WEBHOOK_ID;
    }

    public function store(WebhookInterface $webhook): WebhookInterface
    {
        return $webhook;
    }
}
