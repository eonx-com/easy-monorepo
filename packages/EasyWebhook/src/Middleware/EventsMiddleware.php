<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyWebhook\Events\FailedWebhookEvent;
use EonX\EasyWebhook\Events\FinalFailedWebhookEvent;
use EonX\EasyWebhook\Events\SuccessWebhookEvent;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class EventsMiddleware extends AbstractMiddleware
{
    /**
     * @var string[]
     */
    private const EVENT_CLASSES = [
        WebhookInterface::STATUS_FAILED => FinalFailedWebhookEvent::class,
        WebhookInterface::STATUS_FAILED_PENDING_RETRY => FailedWebhookEvent::class,
        WebhookInterface::STATUS_SUCCESS => SuccessWebhookEvent::class,
    ];

    /**
     * @var \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher, ?int $priority = null)
    {
        $this->dispatcher = $dispatcher;

        parent::__construct($priority);
    }

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $webhookResult = $stack
            ->next()
            ->process($webhook, $stack);

        $status = $webhookResult
            ->getWebhook()
            ->getStatus();

        $eventClass = self::EVENT_CLASSES[$status] ?? null;

        if ($eventClass !== null) {
            $this->dispatcher->dispatch(new $eventClass($webhookResult));
        }

        return $webhookResult;
    }
}
