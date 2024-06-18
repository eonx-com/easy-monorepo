<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Configurator;

use EonX\EasyDecision\Configurator\RestrictedDecisionConfiguratorInterface as RestrictedInterface;
use EonX\EasyDecision\Decision\DecisionInterface;

abstract class AbstractTypeRestrictedDecisionConfigurator extends AbstractDecisionConfigurator implements RestrictedInterface
{
    public function supports(DecisionInterface $decision): bool
    {
        $type = $this->getType();

        return $decision instanceof $type;
    }

    abstract protected function getType(): string;
}
