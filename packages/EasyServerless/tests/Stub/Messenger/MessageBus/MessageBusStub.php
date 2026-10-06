<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Stub\Messenger\MessageBus;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

final class MessageBusStub implements MessageBusInterface
{
    /**
     * @var \Symfony\Component\Messenger\Envelope[]
     */
    private array $dispatchedEnvelopes = [];

    public function __construct(
        private readonly ?Throwable $throwable = null,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function dispatch(object $message, array $stamps = []): Envelope
    {
        $envelope = Envelope::wrap($message, $stamps);
        $this->dispatchedEnvelopes[] = $envelope;

        if ($this->throwable !== null) {
            throw $this->throwable;
        }

        return $envelope;
    }

    /**
     * @return \Symfony\Component\Messenger\Envelope[]
     */
    public function getDispatchedEnvelopes(): array
    {
        return $this->dispatchedEnvelopes;
    }
}
