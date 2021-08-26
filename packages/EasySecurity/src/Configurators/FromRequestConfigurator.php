<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Configurators;

use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasyUtils\CollectorHelper;
use Symfony\Component\HttpFoundation\Request;

final class FromRequestConfigurator
{
    /**
     * @var iterable<\EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface>
     */
    private $configurators;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @param iterable<\EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface> $configurators
     */
    public function __construct(Request $request, iterable $configurators)
    {
        $this->request = $request;
        $this->configurators = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($configurators, SecurityContextConfiguratorInterface::class)
        );
    }

    public function __invoke(SecurityContextInterface $securityContext): SecurityContextInterface
    {
        foreach ($this->configurators as $configurator) {
            $configurator->configure($securityContext, $this->request);
        }

        return $securityContext;
    }
}
