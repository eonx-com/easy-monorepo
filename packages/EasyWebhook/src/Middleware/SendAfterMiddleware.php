<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use Carbon\Carbon;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;
use EonX\EasyWebhook\WebhookResult;

final class SendAfterMiddleware extends AbstractMiddleware
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface
     */
    private $store;

    public function __construct(WebhookResultStoreInterface $store, ?int $priority = null)
    {
        $this->store = $store;

        parent::__construct($priority);
    }

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $sendAfter = $webhook->getSendAfter();

        if ($sendAfter !== null) {
            $now = Carbon::now($sendAfter->getTimezone());

            // If sendAfter is in the future, simply store webhook
            if ($sendAfter > $now) {
                return $this->store->store(new WebhookResult($webhook));
            }
        }

        return $stack
            ->next()
            ->process($webhook, $stack);
    }
}
