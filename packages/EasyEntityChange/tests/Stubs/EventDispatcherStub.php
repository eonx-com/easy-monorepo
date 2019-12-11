<?php
declare(strict_types=1);

namespace EonX\EasyEntityChange\Tests\Stubs;

use EoneoPay\Externals\EventDispatcher\Interfaces\EventDispatcherInterface;

final class EventDispatcherStub implements EventDispatcherInterface
{
    /**
     * Dispatched.
     *
     * @var mixed[]
     */
    private $dispatched = [];

    /**
     * An array of returns from dispatch().
     *
     * @var mixed[]
     */
    private $returns = [];

    /**
     * Add a return value for dispatch.
     *
     * @param mixed[]|null $return
     *
     * @return void
     */
    public function addReturn(?array $return = null): void
    {
        $this->returns[] = $return;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch($event, $payload = null, ?bool $halt = null): ?array
    {
        $this->dispatched[] = \compact('event', 'payload', 'halt');

        return \array_shift($this->returns);
    }

    /**
     * Dispatched events.
     *
     * @return mixed[]
     */
    public function getDispatched(): array
    {
        return $this->dispatched;
    }

    /**
     * {@inheritdoc}
     */
    public function listen(array $events, string $listener): void
    {
    }
}
