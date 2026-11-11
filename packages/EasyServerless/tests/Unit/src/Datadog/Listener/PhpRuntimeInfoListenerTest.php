<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Datadog\Listener;

use EonX\EasyServerless\Datadog\Listener\PhpRuntimeInfoListener;
use EonX\EasyServerless\Tests\Stub\Kernel\FrameworkKernelStub;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use stdClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

use function DDTrace\root_span;

final class PhpRuntimeInfoListenerTest extends AbstractUnitTestCase
{
    private const EVENT_NAMES = [
        ConsoleEvents::COMMAND,
        KernelEvents::REQUEST,
        WorkerMessageReceivedEvent::class,
    ];

    /**
     * @see testListenerAddsPhpRuntimeInfoToRootSpan
     */
    public static function provideEventData(): iterable
    {
        yield 'console command' => [ConsoleEvents::COMMAND, 'onConsoleCommand'];
        yield 'main request' => [KernelEvents::REQUEST, 'onRequest'];
        yield 'worker message received' => [WorkerMessageReceivedEvent::class, 'onWorkerMessageReceived'];
    }

    #[DataProvider('provideEventData')]
    #[RunInSeparateProcess]
    public function testListenerAddsPhpRuntimeInfoToRootSpan(string $eventName, string $method): void
    {
        $this->requireDdTraceFixture();
        $sut = new PhpRuntimeInfoListener();
        $event = match ($eventName) {
            ConsoleEvents::COMMAND => new ConsoleCommandEvent(
                new Command('test'),
                new ArrayInput([]),
                new NullOutput()
            ),
            KernelEvents::REQUEST => new RequestEvent(
                $this->createStub(HttpKernelInterface::class),
                Request::create('/'),
                HttpKernelInterface::MAIN_REQUEST
            ),
            default => new WorkerMessageReceivedEvent(new Envelope(new stdClass()), 'async'),
        };

        $sut->{$method}($event);

        self::assertSame(\PHP_VERSION, root_span()?->meta['php.version'] ?? null);
    }

    public function testListenerIsRegistered(): void
    {
        $kernel = new FrameworkKernelStub(
            'test',
            true,
            [__DIR__ . '/../../../../Fixture/config/framework_array_cache.php']
        );
        $kernel->boot();
        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $kernel->getContainer()
            ->get('event_dispatcher');
        $registeredListeners = [];

        foreach (self::EVENT_NAMES as $eventName) {
            foreach ($eventDispatcher->getListeners($eventName) as $listener) {
                if (\is_callable($listener)
                    && \is_array($listener)
                    && $listener[0] instanceof PhpRuntimeInfoListener) {
                    $registeredListeners[$eventName] = [
                        $listener[1],
                        $eventDispatcher->getListenerPriority($eventName, $listener),
                    ];
                }
            }
        }

        self::assertSame(
            [
                ConsoleEvents::COMMAND => ['onConsoleCommand', 0],
                KernelEvents::REQUEST => ['onRequest', 10_000],
                WorkerMessageReceivedEvent::class => ['onWorkerMessageReceived', 0],
            ],
            $registeredListeners
        );
    }

    #[RunInSeparateProcess]
    public function testOnRequestIgnoresSubRequest(): void
    {
        $this->requireDdTraceFixture();
        $sut = new PhpRuntimeInfoListener();

        $sut->onRequest(new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            Request::create('/'),
            HttpKernelInterface::SUB_REQUEST
        ));

        self::assertSame([], root_span()?->meta);
    }

    private function requireDdTraceFixture(): void
    {
        if (\extension_loaded('ddtrace')) {
            self::markTestSkipped('The ddtrace extension is loaded');
        }

        require_once __DIR__ . '/../../../../Fixture/ddtrace/ddtrace.php';
    }
}
