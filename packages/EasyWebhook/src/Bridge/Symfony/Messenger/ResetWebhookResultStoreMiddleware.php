<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\Messenger;

use EonX\EasyWebhook\Interfaces\ResettableWebhookResultStoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class ResetWebhookResultStoreMiddleware implements MiddlewareInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface
     */
    private $store;

    public function __construct(WebhookResultStoreInterface $store)
    {
        $this->store = $store;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if ($this->store instanceof ResettableWebhookResultStoreInterface) {
            $this->store->reset();
        }

        return $stack->next()
            ->handle($envelope, $stack);
    }
}
