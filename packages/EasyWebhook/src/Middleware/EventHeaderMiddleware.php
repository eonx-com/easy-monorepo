<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class EventHeaderMiddleware extends AbstractConfigureOnceMiddleware
{
    private string $eventHeader;

    public function __construct(?string $eventHeader = null, ?int $priority = null)
    {
        $this->eventHeader = $eventHeader ?? WebhookInterface::HEADER_EVENT;

        parent::__construct($priority);
    }

    protected function doProcess(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $event = $webhook->getEvent() ?? '';

        if ($event !== '') {
            $webhook->header($this->eventHeader, $event);
        }

        return $stack
            ->next()
            ->process($webhook, $stack);
    }
}
