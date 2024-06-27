<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Messenger\MessageHandler;

use EonX\EasyWebhook\Common\Client\WebhookClientInterface;
use EonX\EasyWebhook\Common\Exception\CannotRerunWebhookException;
use EonX\EasyWebhook\Common\Store\StoreInterface;
use EonX\EasyWebhook\Messenger\Exception\UnrecoverableWebhookMessageException;
use EonX\EasyWebhook\Messenger\Message\SendWebhookMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendWebhookMessageHandler
{
    public function __construct(
        private readonly WebhookClientInterface $client,
        private readonly StoreInterface $store,
    ) {
    }

    public function __invoke(SendWebhookMessage $message): void
    {
        $webhook = $this->store->find($message->getWebhookId());

        if ($webhook === null) {
            return;
        }

        // Once here, webhooks are already configured and should be sent synchronously
        try {
            $message->setResult($this->client->sendWebhook($webhook->sendNow(true)));
        } catch (CannotRerunWebhookException $e) {
            throw new UnrecoverableWebhookMessageException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
