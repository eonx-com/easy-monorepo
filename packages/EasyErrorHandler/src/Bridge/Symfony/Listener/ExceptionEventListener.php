<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Listener;

use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class ExceptionEventListener
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface
     */
    private $errorHandler;

    public function __construct(ErrorHandlerInterface $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $this->errorHandler->report($event->getThrowable());

        // Skip if format is html
        if ($event->getRequest()->getRequestFormat('') === 'html') {
            return;
        }

        $event->setResponse($this->errorHandler->render($event->getRequest(), $event->getThrowable()));
    }
}
