<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Laravel\Jobs;

use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookRetryStrategyInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendWebhookJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * @var int
     */
    public $tries;

    /**
     * @var string
     */
    private $webhookId;

    public function __construct(string $webhookId, ?int $tries = null)
    {
        $this->webhookId = $webhookId;
        $this->tries = $tries ?? 1;
    }

    public function handle(
        WebhookClientInterface $client,
        WebhookRetryStrategyInterface $retryStrategy,
        WebhookResultStoreInterface $store
    ): void {
        $result = $store->find($this->webhookId);

        if ($result === null) {
            return;
        }

        // Once here, webhooks are already configured and should be sent synchronously
        $result
            ->getWebhook()
            ->sendNow(true);

        $result = $client->sendWebhook($result->getWebhook());

        if ($result->isSuccessful() === false && $retryStrategy->isRetryable($result->getWebhook())) {
            $this->release($retryStrategy->getWaitingTime($result->getWebhook()) / 1000);
        }
    }
}
