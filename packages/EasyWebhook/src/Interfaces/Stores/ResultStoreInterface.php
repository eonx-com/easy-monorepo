<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces\Stores;

use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

interface ResultStoreInterface
{
    /**
     * @var string
     */
    public const DEFAULT_TABLE = 'easy_webhook_results';

    public function store(WebhookResultInterface $result): WebhookResultInterface;
}
