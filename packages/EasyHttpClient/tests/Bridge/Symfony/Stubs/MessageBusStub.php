<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Bridge\Symfony\Stubs;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessageBusStub implements MessageBusInterface
{
    /**
     * @var \Symfony\Component\Messenger\Envelope[]
     */
    private array $envelopes = [];

    /**
     * @param \Symfony\Component\Messenger\Stamp\StampInterface[]|null $stamps
     */
    public function dispatch(object $message, ?array $stamps = null): Envelope
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
