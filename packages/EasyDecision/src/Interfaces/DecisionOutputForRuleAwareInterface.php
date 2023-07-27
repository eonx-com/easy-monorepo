<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface DecisionOutputForRuleAwareInterface
{
    public function getDecisionOutputForRule(mixed $decisionOutput): mixed;
}
