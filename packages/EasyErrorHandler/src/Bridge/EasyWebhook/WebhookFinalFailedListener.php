<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\EasyWebhook;

use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasyWebhook\Common\Event\FinalFailedWebhookEvent;

final class WebhookFinalFailedListener
{
    public function __construct(
        private readonly ErrorHandlerInterface $errorHandler,
    ) {
    }

    public function __invoke(FinalFailedWebhookEvent $event): void
    {
        $throwable = $event->getResult()
            ->getThrowable();

        if ($throwable === null) {
            return;
        }

        $this->errorHandler->report($throwable);
    }
}
