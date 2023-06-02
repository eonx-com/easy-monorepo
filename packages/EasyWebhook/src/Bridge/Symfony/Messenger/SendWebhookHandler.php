<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\Messenger;

use EonX\EasyWebhook\Bridge\Symfony\Exceptions\UnrecoverableWebhookMessageException;
use EonX\EasyWebhook\Exceptions\CannotRerunWebhookException;
use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SendWebhookHandler implements MessageHandlerInterface
{
    public function __construct(
        readonly private WebhookClientInterface $client,
        readonly private StoreInterface $store,
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
