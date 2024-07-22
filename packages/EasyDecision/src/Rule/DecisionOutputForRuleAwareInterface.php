<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Rule;

interface DecisionOutputForRuleAwareInterface
{
    public function getDecisionOutputForRule(mixed $decisionOutput): mixed;
}
