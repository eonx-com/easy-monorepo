<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

interface WebhookRetryStrategyInterface
{
    public function isRetryable(WebhookInterface $webhook): bool;

    /**
     * @return int The time to delay/wait in milliseconds
     */
    public function getWaitingTime(WebhookInterface $webhook): int;
}
