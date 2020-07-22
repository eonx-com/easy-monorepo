<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;

final class NullWebhookResultStore implements WebhookResultStoreInterface
{
    public function find(string $id): ?WebhookResultInterface
    {
        return null;
    }

    public function store(WebhookResultInterface $result): WebhookResultInterface
    {
        return $result;
    }
}
