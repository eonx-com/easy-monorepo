<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface DecisionOutputForRuleAwareInterface
{
    /**
     * @param mixed $decisionOutput
     *
     * @return mixed
     */
    public function getDecisionOutputForRule($decisionOutput);
}
