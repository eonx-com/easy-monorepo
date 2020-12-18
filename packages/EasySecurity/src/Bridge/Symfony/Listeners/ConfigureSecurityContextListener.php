<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Listeners;

use EonX\EasySecurity\Events\SecurityContextCreatedEvent;
use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface;
use EonX\EasyUtils\CollectorHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ConfigureSecurityContextListener
{
    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface[]
     */
    private $configurators;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @param iterable<mixed> $configurators
     */
    public function __construct(iterable $configurators, RequestStack $requestStack)
    {
        $this->configurators = CollectorHelper::orderLowerPriorityFirst(
            CollectorHelper::filterByClass($configurators, SecurityContextConfiguratorInterface::class)
        );

        $this->requestStack = $requestStack;
    }

    public function __invoke(SecurityContextCreatedEvent $event): void
    {
        $context = $event->getSecurityContext();
        $request = $this->requestStack->getMasterRequest() ?? new Request();

        foreach ($this->configurators as $configurator) {
            $configurator->configure($context, $request);
        }
    }
}
