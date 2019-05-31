<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Decisions;

use LoyaltyCorp\EasyDecision\Interfaces\ContextInterface;
use LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface;
use LoyaltyCorp\EasyDecision\Middleware\YesNoMiddleware;

final class ConsensusDecision extends AbstractDecision
{
    /**
     * Do make decision based on given context.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface $context
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

    /**
     * Get middleware class.
     *
     * @return string
     */
    protected function getMiddlewareClass(): string
    {
        return YesNoMiddleware::class;
    }
}

\class_alias(
    ConsensusDecision::class,
    'StepTheFkUp\EasyDecision\Decisions\ConsensusDecision',
    false
);
