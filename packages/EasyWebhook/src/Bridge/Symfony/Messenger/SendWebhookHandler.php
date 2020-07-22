<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\Messenger;

use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookRetryStrategyInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SendWebhookHandler implements MessageHandlerInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookClientInterface
     */
    private $client;

    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookRetryStrategyInterface
     */
    private $retryStrategy;

    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface
     */
    private $store;

    public function __construct(
        WebhookClientInterface $client,
        WebhookResultStoreInterface $store,
        WebhookRetryStrategyInterface $retryStrategy
    ) {
        $this->client = $client;
        $this->store = $store;
        $this->retryStrategy = $retryStrategy;
    }

    public function __invoke(SendWebhookMessage $message): void
    {
        $result = $this->store->find($message->getWebhookId());

        if ($result === null) {
            return;
        }

        $message->setResult($this->client->sendWebhook($result->getWebhook()->setSendNow(true)));
    }
}
