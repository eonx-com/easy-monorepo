<?php

declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasySecurity\Events\SecurityContextCreatedEvent;
use EonX\EasySecurity\Interfaces\SecurityContextFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;

abstract class AbstractSecurityContextFactory implements SecurityContextFactoryInterface
{
    /**
     * @var null|\EonX\EasySecurity\Interfaces\SecurityContextInterface
     */
    private $cached;

    /**
     * @var null|\EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(?EventDispatcherInterface $eventDispatcher = null)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(): SecurityContextInterface
    {
        if ($this->cached !== null) {
            return $this->cached;
        }

        $context = $this->doCreate();

        if ($this->eventDispatcher !== null) {
            $this->eventDispatcher->dispatch(new SecurityContextCreatedEvent($context));
        }

        return $this->cached = $context;
    }

    public function reset(): void
    {
        $this->cached = null;
    }

    abstract protected function doCreate(): SecurityContextInterface;
}
