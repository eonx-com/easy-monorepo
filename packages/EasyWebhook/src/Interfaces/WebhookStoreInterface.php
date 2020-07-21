<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

interface WebhookStoreInterface
{
    /**
     * @var string
     */
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function find(string $webhookId): ?WebhookInterface;

    public function store(array $data, ?string $id = null): string;
}
