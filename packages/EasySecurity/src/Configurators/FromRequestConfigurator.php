<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Configurators;

use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;
use Symfony\Component\HttpFoundation\Request;

final class FromRequestConfigurator
{
    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface[]
     */
    private array $configurators;

    /**
     * @param iterable<\EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface> $configurators
     */
    public function __construct(
        private readonly Request $request,
        iterable $configurators,
    ) {
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
