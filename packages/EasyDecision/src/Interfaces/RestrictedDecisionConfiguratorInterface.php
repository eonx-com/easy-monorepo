<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface RestrictedDecisionConfiguratorInterface extends DecisionConfiguratorInterface
{
    public function supports(DecisionInterface $decision): bool;
}
