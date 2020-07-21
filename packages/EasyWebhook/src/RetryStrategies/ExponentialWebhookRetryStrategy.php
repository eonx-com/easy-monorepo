<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\RetryStrategies;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultHandlerInterface;
use EonX\EasyWebhook\Interfaces\WebhookRetryStrategyInterface;
use EonX\EasyWebhook\Interfaces\WebhookStoreInterface;

final class ExponentialWebhookRetryStrategy implements WebhookRetryStrategyInterface
{
    /**
     * @var int
     */
    private $max;

    /**
     * @var int
     */
    private $start;

    /**
     * @var int
     */
    private $threshold;

    public function __construct(?int $start = null, ?int $max = null, ?int $threshold = null)
    {
        $this->start = $start ?? 10;
        $this->max = $max ?? 100000;
        $this->threshold = $threshold ?? 4;
    }

    public function failedStatus(WebhookInterface $webhook): string
    {
        return $webhook->getCurrentAttempt() >= $webhook->getMaxAttempt()
            ? WebhookInterface::STATUS_FAILED
            : WebhookInterface::STATUS_FAILED_PENDING_RETRY;
    }

    public function retryAfter(WebhookInterface $webhook): ?\DateTimeInterface
    {
        // If first attempt and retryAfter is set, just return it
        if (($webhook->getCurrentAttempt() ?? 1) === 1 && $webhook->getRetryAfter() !== null) {
            return $webhook->getRetryAfter();
        }

        return $this->getRetryAfter($webhook)->addSeconds($this->getSeconds($webhook));
    }

    private function getRetryAfter(WebhookInterface $webhook): CarbonInterface
    {
        if ($webhook->getRetryAfter() === null) {
            return Carbon::now('UTC');
        }

        $retryAfter = Carbon::createFromFormat(
            WebhookStoreInterface::DATETIME_FORMAT,
            $webhook->getRetryAfter()->format(WebhookStoreInterface::DATETIME_FORMAT),
            $webhook->getRetryAfter()->getTimezone()
        );

        $retryAfter->setTimezone('UTC');

        return $retryAfter;
    }

    private function getSeconds(WebhookInterface $webhook): int
    {
        $attempt = $webhook->getCurrentAttempt() ?? 1;

        return $attempt >= $this->threshold ? $this->max : $this->start ** $attempt;
    }
}
