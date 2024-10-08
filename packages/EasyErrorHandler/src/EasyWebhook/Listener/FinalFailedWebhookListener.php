<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\EasyWebhook\Listener;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyWebhook\Common\Event\FinalFailedWebhookEvent;

final readonly class FinalFailedWebhookListener
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler,
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
