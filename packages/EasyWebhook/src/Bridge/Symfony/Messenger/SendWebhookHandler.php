<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\Messenger;

use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookStoreInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SendWebhookHandler implements MessageHandlerInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookClientInterface
     */
    private $client;

    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookStoreInterface
     */
    private $store;

    public function __construct(WebhookClientInterface $client, WebhookStoreInterface $store)
    {
        $this->client = $client;
        $this->store = $store;
    }

    public function __invoke(SendWebhookMessage $message): void
    {
        $webhook = $this->store->find($message->getWebhookId());

        if ($webhook === null) {
            return;
        }

        $this->client->sendWebhook($webhook->setSendNow(true));
    }
}
