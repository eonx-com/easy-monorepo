<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use Carbon\Carbon;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResult;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Stack\StackInterface;
use EonX\EasyWebhook\Common\Store\StoreInterface;

final class SendAfterMiddleware extends AbstractMiddleware
{
    public function __construct(
        private readonly StoreInterface $store,
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
