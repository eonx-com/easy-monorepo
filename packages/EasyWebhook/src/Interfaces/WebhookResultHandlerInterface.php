<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

interface WebhookResultHandlerInterface
{
    public function handle(WebhookResultInterface $webhookResult): void;
}
