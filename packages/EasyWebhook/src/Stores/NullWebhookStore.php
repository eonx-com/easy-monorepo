<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookStoreInterface;

final class NullWebhookStore implements WebhookStoreInterface
{
    public function find(string $webhookId): ?WebhookInterface
    {
        return null;
    }

    /**
     * @param mixed[] $data
     */
    public function store(array $data, ?string $id = null): string
    {
        return '';
    }
}
