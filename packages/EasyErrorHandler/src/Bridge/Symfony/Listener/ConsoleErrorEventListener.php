<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Listener;

use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use Symfony\Component\Console\Event\ConsoleErrorEvent;

final class ConsoleErrorEventListener
{
    public function __construct(
        private readonly ErrorHandlerInterface $errorHandler,
    ) {
    }

    public function __invoke(ConsoleErrorEvent $event): void
    {
        $this->errorHandler->report($event->getError());
    }
}
