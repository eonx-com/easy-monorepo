<?php
declare(strict_types=1);

namespace EonX\EasyTest\PHPUnit\Subscriber;

use EonX\EasyTest\EasyErrorHandler\Common\ErrorHandler\TraceableErrorHandlerStub;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;

final class HttpClientTestFinishedSubscriber implements FinishedSubscriber
{
    public function notify(Finished $event): void
    {
        TraceableErrorHandlerStub::reset();
    }
}
