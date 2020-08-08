<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\Messenger;

use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SendWebhookHandler implements MessageHandlerInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookClientInterface
     */
    private $client;

    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface
     */
    private $store;

    public function __construct(WebhookClientInterface $client, WebhookResultStoreInterface $store)
    {
        $this->client = $client;
        $this->store = $store;
    }

    public function __invoke(SendWebhookMessage $message): void
    {
        $result = $this->store->find($message->getWebhookId());

        if ($result === null) {
            return;
        }

        $message->setResult($this->client->sendWebhook($result->getWebhook()->sendNow(true)));
    }
}
