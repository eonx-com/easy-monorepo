<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony\Stubs;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

final class MessageBusStub implements MessageBusInterface
{

    public function dispatch(object $message, array $stamps = []): Envelope
    {
        // TODO: Implement dispatch() method.
    }
}
