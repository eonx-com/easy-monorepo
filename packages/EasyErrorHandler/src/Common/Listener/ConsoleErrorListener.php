<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Listener;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use Symfony\Component\Console\Event\ConsoleErrorEvent;

final readonly class ConsoleErrorListener
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler,
    ) {
    }

    public function __invoke(ConsoleErrorEvent $event): void
    {
        $this->errorHandler->report($event->getError());
    }
}
