<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use Carbon\Carbon;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\WebhookResult;

final class SendAfterMiddleware extends AbstractMiddleware
{
    public function __construct(
        private StoreInterface $store,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $sendAfter = $webhook->getSendAfter();

        if ($sendAfter === null || $webhook->isSendAfterBypassed() || $webhook->isSendNow()) {
            return $this->passOn($webhook, $stack);
        }

        $now = Carbon::now($sendAfter->getTimezone());

        // If sendAfter is in the future, simply store webhook
        if ($sendAfter > $now) {
            return new WebhookResult($this->store->store($webhook));
        }

        return $this->passOn($webhook, $stack);
    }
}
