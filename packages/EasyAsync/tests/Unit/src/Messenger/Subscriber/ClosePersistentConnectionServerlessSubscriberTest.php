<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Unit\Messenger\Subscriber;

use stdClass;
use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyAsync\Messenger\Subscriber\ClosePersistentConnectionServerlessSubscriber;
use EonX\EasyAsync\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyServerless\Messenger\Event\EnvelopeDispatchedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

final class ClosePersistentConnectionServerlessSubscriberTest extends AbstractUnitTestCase
{
    public function testClosesConnectionOnNextMessageAfterIdlePeriod(): void
    {
        self::bootKernel(['environment' => 'serverless']);

        $connection = self::getService(EntityManagerInterface::class)->getConnection();
        $connection->getDatabase();

        self::getService(EventDispatcherInterface::class)->dispatch(new EnvelopeDispatchedEvent());
        self::assertTrue($connection->isConnected());

        self::getService(EventDispatcherInterface::class)->dispatch(
            new WorkerMessageReceivedEvent(new Envelope(new stdClass()), 'async')
        );

        self::assertFalse($connection->isConnected());
    }

    public function testDoesNotCloseConnectionWithoutPreviousEnvelopeDispatchedEvent(): void
    {
        self::bootKernel(['environment' => 'serverless']);

        $connection = self::getService(EntityManagerInterface::class)->getConnection();
        $connection->getDatabase();

        self::getService(EventDispatcherInterface::class)->dispatch(
            new WorkerMessageReceivedEvent(new Envelope(new stdClass()), 'async')
        );

        self::assertTrue($connection->isConnected());
    }

    public function testServiceIsRegisteredAsEventSubscriber(): void
    {
        self::bootKernel(['environment' => 'serverless']);

        self::assertTrue(self::getContainer()->has(ClosePersistentConnectionServerlessSubscriber::class));
    }
}
