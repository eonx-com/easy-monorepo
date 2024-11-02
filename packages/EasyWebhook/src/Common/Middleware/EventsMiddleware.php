<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Enum\WebhookStatus;
use EonX\EasyWebhook\Common\Event\FailedWebhookEvent;
use EonX\EasyWebhook\Common\Event\FinalFailedWebhookEvent;
use EonX\EasyWebhook\Common\Event\SuccessWebhookEvent;
use EonX\EasyWebhook\Common\Stack\StackInterface;

final class EventsMiddleware extends AbstractMiddleware
{
    private const EVENT_CLASSES = [
        WebhookStatus::FailedPendingRetry->value => FailedWebhookEvent::class,
        WebhookStatus::Failed->value => FinalFailedWebhookEvent::class,
        WebhookStatus::Success->value => SuccessWebhookEvent::class,
    ];

    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $webhookResult = $this->passOn($webhook, $stack);

        $status = $webhookResult->getWebhook()
            ->getStatus();

        $eventClass = self::EVENT_CLASSES[$status->value] ?? null;

        if ($eventClass !== null) {
            $this->dispatcher->dispatch(new $eventClass($webhookResult));
        }

        return $webhookResult;
    }
}
