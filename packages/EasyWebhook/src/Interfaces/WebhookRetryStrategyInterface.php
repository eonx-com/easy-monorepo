<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

interface WebhookRetryStrategyInterface
{
    public function failedStatus(WebhookInterface $webhook): string;

    public function retryAfter(WebhookInterface $webhook): ?\DateTimeInterface;
}
