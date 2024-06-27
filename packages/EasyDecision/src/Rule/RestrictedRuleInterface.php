<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Rule;

use EonX\EasyDecision\Decision\DecisionInterface;

interface RestrictedRuleInterface extends RuleInterface
{
    public function supportsDecision(DecisionInterface $decision): bool;
}
