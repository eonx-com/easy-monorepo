<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Listener;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Common\ErrorHandler\FormatAwareInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final readonly class ExceptionListener
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $throwable = $event->getThrowable();

        $this->errorHandler->report($throwable);

        // Skip if format not supported
        if ($this->errorHandler instanceof FormatAwareInterface &&
            $this->errorHandler->supportsFormat($request) === false
        ) {
            return;
        }

        $event->allowCustomResponseCode();
        $event->setResponse($this->errorHandler->render($request, $throwable));
    }
}
