<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Configurator;

use EonX\EasySecurity\Common\Context\SecurityContextInterface;
use EonX\EasyUtils\Common\Helper\CollectorHelper;
use Symfony\Component\HttpFoundation\Request;

final class FromRequestConfigurator
{
    /**
     * @var \EonX\EasySecurity\Common\Configurator\SecurityContextConfiguratorInterface[]
     */
    private readonly array $configurators;

    /**
     * @param iterable<\EonX\EasySecurity\Common\Configurator\SecurityContextConfiguratorInterface> $configurators
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
