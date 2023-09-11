<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface RestrictedRuleInterface extends RuleInterface
{
    public function supportsDecision(DecisionInterface $decision): bool;
}
