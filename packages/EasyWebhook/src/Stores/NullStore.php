<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;

final class NullStore implements StoreInterface
{
    public function find(string $id): ?WebhookInterface
    {
        return null;
    }

    public function generateWebhookId(): string
    {
        return 'webhook-id';
    }

    public function store(WebhookInterface $webhook): WebhookInterface
    {
        return $webhook;
    }
}
