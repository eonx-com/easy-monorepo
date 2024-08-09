<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\EasyErrorHandler\Listener;

use EonX\EasyDoctrine\EntityEvent\Event\WrapInTransactionExceptionEvent;
use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final readonly class WrapInTransactionExceptionListener
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler,
    ) {
    }

    public function __invoke(WrapInTransactionExceptionEvent $event): void
    {
        $this->errorHandler->report($event->getThrowable());
    }
}
