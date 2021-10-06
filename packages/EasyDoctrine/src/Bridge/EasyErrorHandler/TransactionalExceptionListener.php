<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\EasyErrorHandler;

use EonX\EasyDoctrine\Events\TransactionalExceptionEvent;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;

final class TransactionalExceptionListener
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface
     */
    private $errorHandler;

    public function __construct(ErrorHandlerInterface $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function __invoke(TransactionalExceptionEvent $event)
    {
        $this->errorHandler->report($event->getException());
    }
}
