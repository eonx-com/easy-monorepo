<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Stubs;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class MessengerMiddlewareStub implements MiddlewareInterface
{
    /**
     * @var callable
     */
    private $func;

    public function __construct(callable $func)
    {
        $this->func = $func;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        return \call_user_func($this->func);
    }
}
