<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\EasyErrorHandler;

use EonX\EasyDoctrine\Events\TransactionalExceptionEvent;
use EonX\EasyErrorHandler\ErrorHandler;

final class TransactionalExceptionListener
{
    /**
     * @var \EonX\EasyErrorHandler\ErrorHandler
     */
    private $errorHandler;

    public function __construct(ErrorHandler $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function __invoke(TransactionalExceptionEvent $event)
    {
        $this->errorHandler->report($event->getException());
    }
}
