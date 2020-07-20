<?php

declare(strict_types=1);

namespace EonX\EasyWebhooks\RetryStrategies;

use EonX\EasyWebhooks\Interfaces\WebhookInterface;
use EonX\EasyWebhooks\Interfaces\WebhookRetryStrategyInterface;

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
