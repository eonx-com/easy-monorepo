<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Laravel\Jobs;

use EonX\EasyLock\Interfaces\LockDataInterface;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyLock\Interfaces\WithLockDataInterface;
use EonX\EasyLock\LockData;
use EonX\EasyLock\ProcessWithLockTrait;
use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookRetryStrategyInterface;
use EonX\EasyWebhook\Interfaces\WebhookStoreInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendWebhookJob implements ShouldQueue, WithLockDataInterface
{
    use InteractsWithQueue;
    use Queueable;
    use ProcessWithLockTrait;

    /**
     * @var string
     */
    private $webhookId;

    public function __construct(string $webhookId)
    {
        $this->webhookId = $webhookId;
    }

    public function getLockData(): LockDataInterface
    {
        return LockData::create(\sprintf('easy_webhook_send_%s', $this->webhookId));
    }

    public function handle(
        LockServiceInterface $lockService,
        WebhookClientInterface $client,
        WebhookRetryStrategyInterface $retryStrategy,
        WebhookStoreInterface $store
    ): void {
        $this->setLockService($lockService);

        $this->processWithLock($this, function () use ($client, $retryStrategy, $store): void {
            $webhook = $store->find($this->webhookId);

            if ($webhook === null) {
                return;
            }

            $result = $client->sendWebhook($webhook->setSendNow(true));

            if ($result->isSuccessful() === false && $retryStrategy->isRetryable($webhook)) {
                $this->release($retryStrategy->getWaitingTime($webhook) / 1000);
            }
        });
    }
}
