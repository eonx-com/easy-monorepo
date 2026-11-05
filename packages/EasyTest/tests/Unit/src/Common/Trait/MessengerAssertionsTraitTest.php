<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests\Unit\src\Common\Trait;

use ArrayIterator;
use EonX\EasyTest\Common\Trait\MessengerAssertionsTrait;
use EonX\EasyTest\Tests\Fixture\Message\FailingDummyMessage;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;
use Symfony\Component\Messenger\EventListener\AddErrorDetailsStampListener;
use Symfony\Component\Messenger\EventListener\ResetServicesListener;
use Symfony\Component\Messenger\EventListener\SendFailedMessageForRetryListener;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Retry\MultiplierRetryStrategy;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;

final class MessengerAssertionsTraitTest extends KernelTestCase
{
    use ClockSensitiveTrait;
    use MessengerAssertionsTrait;

    private static ?Container $containerStub = null;

    protected function tearDown(): void
    {
        self::$containerStub = null;

        parent::tearDown();
    }

    public function testItFailsWhenFewerExceptionsWereExpectedThanThrown(): void
    {
        $messageBus = $this->arrangeMessenger();
        $messageBus->dispatch(new FailingDummyMessage());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Exception was thrown during messages processing.');

        self::consumeAsyncMessages(
            expectedExceptions: [
                [RuntimeException::class => 77],
            ],
            expectedDelays: [1, 2, 4],
        );
    }

    public function testItSeesEveryRetryOfTheSameException(): void
    {
        $messageBus = $this->arrangeMessenger();
        $messageBus->dispatch(new FailingDummyMessage());

        $this->expectNotToPerformAssertions();

        self::consumeAsyncMessages(
            expectedExceptions: [
                [RuntimeException::class => 77],
                [RuntimeException::class => 77],
                [RuntimeException::class => 77],
                [RuntimeException::class => 77],
            ],
            expectedDelays: [1, 2, 4],
        );
    }

    protected static function getContainer(): Container
    {
        return self::$containerStub ?? parent::getContainer();
    }

    private function arrangeMessenger(): MessageBusInterface
    {
        $transport = new InMemoryTransport(null, self::mockTime());
        $handlersLocator = new HandlersLocator([
            FailingDummyMessage::class => [static function (): void {
                throw new RuntimeException('boom', 77);
            }],
        ]);
        $sendersContainer = new Container();
        $sendersContainer->set('async', $transport);
        $messageBus = new MessageBus([
            new SendMessageMiddleware(new SendersLocator([FailingDummyMessage::class => ['async']], $sendersContainer)),
            new HandleMessageMiddleware($handlersLocator),
        ]);
        $retryStrategyContainer = new Container();
        $retryStrategyContainer->set('async', new MultiplierRetryStrategy(3, 1000, 2, 0, 0));
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new AddErrorDetailsStampListener());
        $eventDispatcher->addSubscriber(
            new SendFailedMessageForRetryListener($sendersContainer, $retryStrategyContainer)
        );

        self::$containerStub = new Container();
        self::$containerStub->set('messenger.transport.async', $transport);
        self::$containerStub->set('messenger.bus.default', $messageBus);
        self::$containerStub->set(EventDispatcherInterface::class, $eventDispatcher);
        self::$containerStub->set('messenger.listener.reset_services', new ResetServicesListener(
            new ServicesResetter(new ArrayIterator([]), [])
        ));

        return $messageBus;
    }
}
