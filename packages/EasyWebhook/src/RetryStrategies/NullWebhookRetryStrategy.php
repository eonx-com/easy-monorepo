<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\RetryStrategies;

use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookRetryStrategyInterface;

final class NullWebhookRetryStrategy implements WebhookRetryStrategyInterface
{
    public function failedStatus(WebhookInterface $webhook): string
    {
        return WebhookInterface::STATUS_FAILED;
    }

    public function retryAfter(WebhookInterface $webhook): ?\DateTimeInterface
    {
        return null;
    }
}
