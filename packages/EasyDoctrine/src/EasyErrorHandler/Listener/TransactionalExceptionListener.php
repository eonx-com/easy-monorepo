<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\EasyErrorHandler\Listener;

use EonX\EasyDoctrine\EntityEvent\Event\TransactionalExceptionEvent;
use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;

final readonly class TransactionalExceptionListener
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler,
    ) {
    }

    public function __invoke(TransactionalExceptionEvent $event): void
    {
        $this->errorHandler->report($event->getThrowable());
    }
}
