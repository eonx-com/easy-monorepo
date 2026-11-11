<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Unit\Messenger\Listener;

use EonX\EasyErrorHandler\Common\Exception\RetryableException;
use EonX\EasyErrorHandler\Messenger\Listener\ServerlessWorkerMessageFailedListener;
use EonX\EasyErrorHandler\Tests\Stub\ErrorHandler\ErrorHandlerStub;
use EonX\EasyErrorHandler\Tests\Stub\Resolver\ErrorDetailsResolverStub;
use EonX\EasyErrorHandler\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyServerless\Event\ServerlessWorkerMessageFailedEvent;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use stdClass;
use Symfony\Component\Messenger\Envelope;

final class ServerlessWorkerMessageFailedListenerTest extends AbstractUnitTestCase
{
    #[TestWith([false])]
    #[TestWith([true])]
    public function testInvokeReportsRetryableException(bool $willRetry): void
    {
        $throwable = new RuntimeException('Failure');
        $event = new ServerlessWorkerMessageFailedEvent(new Envelope(new stdClass()), 'async', $throwable);

        if ($willRetry) {
            $event->setForRetry();
        }

        $errorDetailsResolver = new ErrorDetailsResolverStub();
        $errorHandler = new ErrorHandlerStub();
        $sut = new ServerlessWorkerMessageFailedListener($errorDetailsResolver, $errorHandler);

        $sut($event);

        self::assertSame(1, $errorDetailsResolver->getResetCount());
        self::assertCount(1, $errorHandler->getReportedThrowables());
        $reported = $errorHandler->getReportedThrowables()[0];
        self::assertInstanceOf(RetryableException::class, $reported);
        self::assertSame($willRetry, $reported->willRetry());
        self::assertSame($throwable, $reported->getPrevious());
    }
}
