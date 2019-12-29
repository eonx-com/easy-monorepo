<?php
declare(strict_types=1);

namespace EonX\EasyEntityChange\Tests\Stubs;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @coversNothing
 */
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
    public function dispatch(object $event): object
    {
        $this->dispatched[] = $event;

        return $event;
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
}
