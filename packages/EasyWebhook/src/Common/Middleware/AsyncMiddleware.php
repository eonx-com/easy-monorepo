<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use EonX\EasyWebhook\Common\Dispatcher\AsyncDispatcherInterface;
use EonX\EasyWebhook\Common\Entity\ShouldNotBeStoredWebhookResult;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Exception\WebhookIdRequiredForAsyncException;
use EonX\EasyWebhook\Common\Stack\StackInterface;
use EonX\EasyWebhook\Common\Store\StoreInterface;

final class AsyncMiddleware extends AbstractMiddleware
{
    private readonly bool $enabled;

    public function __construct(
        private readonly AsyncDispatcherInterface $dispatcher,
        private readonly StoreInterface $store,
        ?bool $enabled = null,
        ?int $priority = null,
    ) {
        $this->enabled = $enabled ?? true;

        parent::__construct($priority);
    }

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        if ($this->enabled === false || $webhook->isSendNow()) {
            // If async disabled, make sure webhook is sendNow
            $webhook->sendNow(true);

            return $this->passOn($webhook, $stack);
        }

        $webhook = $this->store->store($webhook);

        if ($webhook->getId() === null) {
            throw new WebhookIdRequiredForAsyncException(\sprintf('
                Webhook must be persisted and have a unique identifier before being sent asynchronously.
                Please verify your %s implementation sets this identifier and is registered as a service
            ', StoreInterface::class));
        }

        $this->dispatcher->dispatch($webhook);

        return new ShouldNotBeStoredWebhookResult($webhook);
    }
}
