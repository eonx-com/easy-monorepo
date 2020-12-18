<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel\Listeners;

use EonX\EasySecurity\Events\SecurityContextCreatedEvent;
use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface;
use EonX\EasyUtils\CollectorHelper;
use Illuminate\Http\Request;

final class ConfigureSecurityContextListener
{
    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface[]
     */
    private $configurators;

    /**
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * @param iterable<mixed> $configurators
     */
    public function __construct(iterable $configurators, Request $request)
    {
        $this->configurators = CollectorHelper::orderLowerPriorityFirst(
            CollectorHelper::filterByClass($configurators, SecurityContextConfiguratorInterface::class)
        );

        $this->request = $request;
    }

    public function handle(SecurityContextCreatedEvent $event): void
    {
        $context = $event->getSecurityContext();

        foreach ($this->configurators as $configurator) {
            $configurator->configure($context, $this->request);
        }
    }
}
