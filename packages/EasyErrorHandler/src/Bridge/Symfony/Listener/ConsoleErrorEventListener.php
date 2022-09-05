<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Listener;

use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use Symfony\Component\Console\Event\ConsoleErrorEvent;

final class ConsoleErrorEventListener
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface
     */
    private $errorHandler;

    public function __construct(ErrorHandlerInterface $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function __invoke(ConsoleErrorEvent $event): void
    {
        $this->errorHandler->report($event->getError());
    }
}
