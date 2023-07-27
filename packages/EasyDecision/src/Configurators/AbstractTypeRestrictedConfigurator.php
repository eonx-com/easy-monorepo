<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Configurators;

use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Interfaces\RestrictedDecisionConfiguratorInterface as RestrictedInterface;

abstract class AbstractTypeRestrictedConfigurator extends AbstractConfigurator implements RestrictedInterface
{
    public function supports(DecisionInterface $decision): bool
    {
        $type = $this->getType();

        return $decision instanceof $type;
    }

    abstract protected function getType(): string;
}
