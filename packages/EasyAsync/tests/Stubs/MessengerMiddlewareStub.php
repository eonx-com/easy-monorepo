<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Stubs;

use Closure;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class MessengerMiddlewareStub implements MiddlewareInterface
{
    private Closure $func;

    public function __construct(callable $func)
    {
        $this->func = $func(...);
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        \call_user_func($this->func);

        return $envelope;
    }
}
