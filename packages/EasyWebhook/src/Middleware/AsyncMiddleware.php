<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyWebhook\Exceptions\WebhookIdRequiredForAsyncException;
use EonX\EasyWebhook\Interfaces\AsyncDispatcherInterface;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\WebhookResult;

final class AsyncMiddleware extends AbstractMiddleware
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\AsyncDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var \EonX\EasyWebhook\Interfaces\Stores\StoreInterface
     */
    private $store;

    public function __construct(
        AsyncDispatcherInterface $dispatcher,
        StoreInterface $store,
        ?bool $enabled = null,
        ?int $priority = null
    ) {
        $this->dispatcher = $dispatcher;
        $this->store = $store;
        $this->enabled = $enabled ?? true;

        parent::__construct($priority);
    }

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        if ($this->enabled === false || $webhook->isSendNow()) {
            return $stack
                ->next()
                ->process($webhook, $stack);
        }

        $webhook = $this->store->store($webhook);

        if ($webhook->getId() === null) {
            throw new WebhookIdRequiredForAsyncException(\sprintf('
                WebhookResult must be persisted and have a unique identifier before being sent asynchronously.
                Please verify your %s implementation sets this identifier and is registered as a service
            ', WebhookResultStoreInterface::class));
        }

        $this->dispatcher->dispatch($webhook);

        return new WebhookResult($webhook);
    }
}
