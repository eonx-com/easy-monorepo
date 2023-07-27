<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Configurators;

use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Interfaces\RestrictedDecisionConfiguratorInterface as RestrictedInterface;

abstract class AbstractNameRestrictedConfigurator extends AbstractConfigurator implements RestrictedInterface
{
    public function supports(DecisionInterface $decision): bool
    {
        return \in_array($decision->getName(), $this->getNames(), true);
    }

    /**
     * @return string[]
     */
    abstract protected function getNames(): array;
}
