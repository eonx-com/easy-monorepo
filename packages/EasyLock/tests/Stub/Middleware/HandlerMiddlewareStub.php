<?php
declare(strict_types=1);

namespace EonX\EasyLock\Tests\Stub\Middleware;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class HandlerMiddlewareStub implements MiddlewareInterface
{
    private int $handleCallCount = 0;

    public function getHandleCallCount(): int
    {
        return $this->handleCallCount;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        ++$this->handleCallCount;

        return $envelope->with(new HandledStamp('result', 'handler'));
    }
}
