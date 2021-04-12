<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\EasyWebhook;

use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasyWebhook\Events\FinalFailedWebhookEvent;

final class WebhookFinalFailedListener
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface
     */
    private $errorHandler;

    public function __construct(ErrorHandlerInterface $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function __invoke(FinalFailedWebhookEvent $event): void
    {
        $this->handle($event);
    }

    public function handle(FinalFailedWebhookEvent $event): void
    {
        $throwable = $event->getResult()
            ->getThrowable();

        if ($throwable === null) {
            return;
        }

        $this->errorHandler->report($throwable);
    }
}
