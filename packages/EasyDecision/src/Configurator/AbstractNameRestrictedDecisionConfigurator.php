<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Configurator;

use EonX\EasyDecision\Decision\DecisionInterface;

abstract class AbstractNameRestrictedDecisionConfigurator extends AbstractDecisionConfigurator
    implements RestrictedDecisionConfiguratorInterface
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
