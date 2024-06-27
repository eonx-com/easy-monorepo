<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Listener;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use Symfony\Component\Console\Event\ConsoleErrorEvent;

final class ConsoleErrorListener
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
