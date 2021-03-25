<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces\Stores;

use EonX\EasyWebhook\Interfaces\WebhookInterface;

interface StoreInterface
{
    /**
     * @var string
     */
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var string
     */
    public const DEFAULT_TABLE = 'easy_webhooks';

    /**
     * @var string
     */
    public const DEFAULT_WEBHOOK_ID = 'webhook-id';

    public function find(string $id): ?WebhookInterface;

    public function generateWebhookId(): string;

    public function store(WebhookInterface $webhook): WebhookInterface;
}
