<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Store;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;

interface StoreInterface
{
    final public const string DATETIME_FORMAT = 'Y-m-d H:i:s';

    final public const array DEFAULT_COLUMNS = [
        'class',
        'created_at',
        'current_attempt',
        'event',
        'http_options',
        'id',
        'max_attempt',
        'method',
        'send_after',
        'status',
        'updated_at',
        'url',
    ];

    final public const string DEFAULT_WEBHOOK_ID = 'webhook-id';

    public function find(string $id): ?WebhookInterface;

    public function generateWebhookId(): string;

    public function store(WebhookInterface $webhook): WebhookInterface;
}
