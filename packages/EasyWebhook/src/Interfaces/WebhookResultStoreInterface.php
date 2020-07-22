<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

interface WebhookResultStoreInterface
{
    /**
     * @var string
     */
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function find(string $id): ?WebhookResultInterface;

    public function store(WebhookResultInterface $result): WebhookResultInterface;
}
