<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Configurator;

use EonX\EasyDecision\Decision\DecisionInterface;

interface RestrictedDecisionConfiguratorInterface extends DecisionConfiguratorInterface
{
    public function supports(DecisionInterface $decision): bool;
}
