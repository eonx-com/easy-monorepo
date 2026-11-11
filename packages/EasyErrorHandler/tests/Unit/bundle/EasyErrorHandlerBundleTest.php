<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Unit\Bundle;

use EonX\EasyErrorHandler\Bugsnag\Provider\BugsnagErrorReporterProvider;
use EonX\EasyErrorHandler\Messenger\Listener\ServerlessWorkerMessageFailedListener;
use EonX\EasyErrorHandler\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyServerless\Event\ServerlessWorkerMessageFailedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class EasyErrorHandlerBundleTest extends AbstractUnitTestCase
{
    public function testItRegistersServerlessWorkerMessageFailedListener(): void
    {
        $eventDispatcher = self::getService(EventDispatcherInterface::class);

        $listeners = $eventDispatcher->getListeners(ServerlessWorkerMessageFailedEvent::class);

        self::assertCount(1, $listeners);
        self::assertIsArray($listeners[0]);
        self::assertInstanceOf(ServerlessWorkerMessageFailedListener::class, $listeners[0][0]);
    }

    public function testItSucceedsWithEasyBugsnag(): void
    {
        self::bootKernel(['environment' => 'with_easy_bugsnag']);

        self::getService(BugsnagErrorReporterProvider::class);

        self::assertTrue(true);
    }
}
