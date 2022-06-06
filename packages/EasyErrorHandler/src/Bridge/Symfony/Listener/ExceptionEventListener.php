<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Listener;

use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\TranslatableExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class ExceptionEventListener
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler,
        private TranslatorInterface $translator
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        if ($throwable instanceof TranslatableExceptionInterface) {
            $throwable->setMessage($this->translator->trans($throwable->getMessage(), $throwable->getMessageParams()));
        }

        $this->errorHandler->report($throwable);

        // Skip if format is html
        if ($event->getRequest()->getRequestFormat('') === 'html') {
            return;
        }

        $event->setResponse($this->errorHandler->render($event->getRequest(), $throwable));
    }
}
