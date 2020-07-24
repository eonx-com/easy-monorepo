<?php

declare(strict_types=1);

namespace EonX\EasyWebhook;

use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultHandlerInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;
use EonX\EasyWebhook\RetryStrategies\NullWebhookRetryStrategy;

final class WebhookResultHandler implements WebhookResultHandlerInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface
     */
    private $store;

    public function __construct(WebhookResultStoreInterface $store)
    {
        $this->store = $store;
    }

    public function handle(WebhookResultInterface $webhookResult): WebhookResultInterface
    {
        $webhook = $webhookResult->getWebhook();
        $webhook->currentAttempt($webhook->getCurrentAttempt() + 1);

        switch ($webhookResult->isSuccessful()) {
            case true:
                $webhook->status(WebhookInterface::STATUS_SUCCESS);
                break;
            case false:
                $webhook->status(
                    $webhook->getCurrentAttempt() >= $webhook->getMaxAttempt()
                        ? WebhookInterface::STATUS_FAILED
                        : WebhookInterface::STATUS_FAILED_PENDING_RETRY
                );
        }

        return $this->store->store($webhookResult);
    }
}
