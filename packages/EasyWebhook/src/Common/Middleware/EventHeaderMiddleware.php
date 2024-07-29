<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Stack\StackInterface;

final class EventHeaderMiddleware extends AbstractConfigureOnceMiddleware
{
    private readonly string $eventHeader;

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
