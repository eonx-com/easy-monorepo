<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests\Unit\Messenger\Factory;

use EonX\EasyTest\Messenger\Factory\InMemoryPersistentTransportFactory;
use EonX\EasyTest\Tests\Fixture\Message\DummyMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Contracts\Service\ResetInterface;

final class InMemoryPersistentTransportFactoryTest extends TestCase
{
    public function testItCreatesInMemoryTransportsOnTheGivenClock(): void
    {
        $clock = new MockClock();
        $factory = new InMemoryPersistentTransportFactory($clock);

        $transport = $factory->createTransport('in-memory-persistent://', [], new PhpSerializer());

        self::assertInstanceOf(InMemoryTransport::class, $transport);
        $transport->send(new Envelope(new DummyMessage(), [new DelayStamp(10_000)]));
        self::assertCount(0, [...$transport->get(\PHP_INT_MAX)]);
        $clock->sleep(10.000001);
        self::assertCount(1, [...$transport->get(\PHP_INT_MAX)]);
    }

    public function testItIsNotResettableSoItsTransportsSurviveServiceResets(): void
    {
        $factory = new InMemoryPersistentTransportFactory();

        self::assertNotContains(ResetInterface::class, \class_implements($factory));
    }

    public function testItSupportsOnlyThePersistentDsn(): void
    {
        $factory = new InMemoryPersistentTransportFactory();

        self::assertTrue($factory->supports('in-memory-persistent://', []));
        self::assertFalse($factory->supports('in-memory://', []));
    }
}
