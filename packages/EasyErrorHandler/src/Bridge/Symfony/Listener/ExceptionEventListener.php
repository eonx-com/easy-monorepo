<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Listener;

use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Interfaces\FormatAwareInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class ExceptionEventListener
{
    public function __construct(private readonly ErrorHandlerInterface $errorHandler)
    {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $this->errorHandler->report($event->getThrowable());

        // Skip if format not supported
        if ($this->errorHandler instanceof FormatAwareInterface
            && $this->errorHandler->supportsFormat($event->getRequest()) === false) {
            return;
        }

        $event->setResponse($this->errorHandler->render($event->getRequest(), $event->getThrowable()));
    }
}
