<?php
declare(strict_types=1);

namespace EonX\EasyTest\PHPUnit\Subscriber;

use EonX\EasyTest\EasyErrorHandler\ErrorHandler\TraceableErrorHandlerStub;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;

final class EasyErrorHandlerTestFinishedSubscriber implements FinishedSubscriber
{
    public function notify(Finished $event): void
    {
        TraceableErrorHandlerStub::reset();
    }
}
