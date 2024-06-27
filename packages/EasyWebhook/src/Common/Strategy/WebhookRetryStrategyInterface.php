<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Strategy;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;

interface WebhookRetryStrategyInterface
{
    /**
     * @return int The time to delay/wait in milliseconds
     */
    public function getWaitingTime(WebhookInterface $webhook): int;

    public function isRetryable(WebhookInterface $webhook): bool;
}
