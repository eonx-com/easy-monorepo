<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces;

interface RuleProviderInterface
{
    /**
     * Get rules.
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\RuleInterface[]
     */
    public function getRules(): array;
}

\class_alias(
    RuleProviderInterface::class,
    'StepTheFkUp\EasyDecision\Interfaces\RuleProviderInterface',
    false
);
