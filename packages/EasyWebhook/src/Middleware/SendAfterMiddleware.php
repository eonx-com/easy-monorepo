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
    /**
     * @var \EonX\EasyWebhook\Interfaces\Stores\StoreInterface
     */
    private $store;

    public function __construct(StoreInterface $store, ?int $priority = null)
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
                return new WebhookResult($this->store->store($webhook));
            }
        }

        return $stack
            ->next()
            ->process($webhook, $stack);
    }
}
