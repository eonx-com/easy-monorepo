<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony\Stubs;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessageBusStub implements MessageBusInterface
{
    /**
     * @param \Symfony\Component\Messenger\Stamp\StampInterface[]|null $stamps
     */
    public function dispatch(object $message, ?array $stamps = null): Envelope
    {
        return Envelope::wrap($message);
    }
}
