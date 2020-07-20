<?php

declare(strict_types=1);

namespace EonX\EasyWebhooks;

use EonX\EasyWebhooks\Interfaces\WebhookInterface;
use EonX\EasyWebhooks\Interfaces\WebhookResultHandlerInterface;
use EonX\EasyWebhooks\Interfaces\WebhookResultInterface;
use EonX\EasyWebhooks\Interfaces\WebhookRetryStrategyInterface;
use EonX\EasyWebhooks\Interfaces\WebhookStoreInterface;
use EonX\EasyWebhooks\RetryStrategies\NullWebhookRetryStrategy;

final class WebhookResultHandler implements WebhookResultHandlerInterface
{
    /**
     * @var \EonX\EasyWebhooks\Interfaces\WebhookRetryStrategyInterface
     */
    private $retryStrategy;

    /**
     * @var \EonX\EasyWebhooks\Interfaces\WebhookStoreInterface
     */
    private $store;

    public function __construct(WebhookStoreInterface $store, ?WebhookRetryStrategyInterface $retryStrategy = null)
    {
        $this->store = $store;
        $this->retryStrategy = $retryStrategy ?? new NullWebhookRetryStrategy();
    }

    public function handle(WebhookResultInterface $webhookResult): void
    {
        $webhook = $webhookResult->getWebhook();
        $response = $webhookResult->getResponse();
        $throwable = $webhookResult->getThrowable();

        $webhook->setCurrentAttempt($webhook->getCurrentAttempt() + 1);

        switch ($webhookResult->isSuccessful()) {
            case true:
                $webhook->setStatus(WebhookInterface::STATUS_SUCCESS);
                break;
            case false:
                $webhook->setStatus($this->retryStrategy->failedStatus($webhook));
                $webhook->setRetryAfter($this->retryStrategy->retryAfter($webhook));
        }

        // Merge extra so each of them is separate column
        $data = \array_merge($webhook->getExtra() ?? [], [
            'current_attempt' => $webhook->getCurrentAttempt(),
            'http_options' => $webhook->getHttpClientOptions(),
            'max_attempt' => $webhook->getMaxAttempt(),
            'method' => $webhook->getMethod(),
            'retry_after' => $webhook->getRetryAfter(),
            'status' => $webhook->getStatus(),
            'url' => $webhook->getUrl(),
        ]);

        if ($response !== null) {
            $data['response'] = [
                'content' => $response->getContent(),
                'headers' => $response->getHeaders(),
                'info' => $response->getInfo(),
                'status_code' => $response->getStatusCode(),
            ];
        }

        if ($throwable !== null) {
            $data['throwable'] = [
                'code' => $throwable->getCode(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'message' => $throwable->getMessage(),
                'trace' => $throwable->getTraceAsString(),
            ];
        }

        $this->store->store($data, $webhook->getId());
    }
}
