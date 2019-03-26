<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Decisions;

use StepTheFkUp\EasyDecision\Interfaces\ContextInterface;
use StepTheFkUp\EasyDecision\Interfaces\DecisionInterface;

final class ConsensusDecision extends AbstractDecision
{
    /**
     * Do make decision based on given context.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\ContextInterface $context
     *
     * @return void
     */
    protected function doMake(ContextInterface $context): void
    {
        $true = 0;
        $false = 0;

        foreach ($context->getRuleOutputs() as $output) {
            if ($output === true) {
                $true++;
                continue;
            }

            if ($output === false) {
                $false++;
            }
        }

        $context->setInput($true >= $false);
    }

    /**
     * Get decision type.
     *
     * @return string
     */
    protected function getDecisionType(): string
    {
        return DecisionInterface::TYPE_YESNO_CONSENSUS;
    }
}
