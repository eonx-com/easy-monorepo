<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Bridge\Symfony\Stubs;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

final class MessageBusStub implements MessageBusInterface
{
    /**
     * @var mixed[]
     */
    private $envelopes = [];

    /**
     * @param object|Envelope $message The message or the message pre-wrapped in an envelope
     * @param StampInterface[] $stamps
     */
    public function dispatch($message, ?array $stamps = null): Envelope
    {
        $envelope = Envelope::wrap($message, $stamps ?? []);

        $this->envelopes[] = $envelope;

        return $envelope;
    }

    /**
     * @return \Symfony\Component\Messenger\Envelope[]
     */
    public function getEnvelopes(): array
    {
        return $this->envelopes;
    }
}
