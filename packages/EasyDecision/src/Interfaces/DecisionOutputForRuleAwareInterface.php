<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface DecisionOutputForRuleAwareInterface
{
    /**
     * Returns rule output for given decision output.
     *
     * @param mixed $decisionOutput
     *
     * @return mixed
     */
    public function getDecisionOutputForRule($decisionOutput);
}
