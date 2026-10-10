<?php
declare(strict_types=1);

namespace EonX\EasyTest\Messenger\Factory;

use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * Creates in-memory transports that survive service resets: unlike the built-in in-memory factory, this one is
 * deliberately not resettable, so messages are not wiped while MessengerAssertionsTrait is consuming them.
 *
 * @implements \Symfony\Component\Messenger\Transport\TransportFactoryInterface<\Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport>
 */
#[AutoconfigureTag('messenger.transport_factory')]
final readonly class InMemoryPersistentTransportFactory implements TransportFactoryInterface
{
    private const string DSN = 'in-memory-persistent://';

    public function __construct(
        private ?ClockInterface $clock = null,
    ) {}

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        return new InMemoryTransport(null, $this->clock);
    }

    public function supports(string $dsn, array $options): bool
    {
        return \str_starts_with($dsn, self::DSN);
    }
}
