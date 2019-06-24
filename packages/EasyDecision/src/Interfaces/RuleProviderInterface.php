<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces;

interface RuleProviderInterface
{
    /**
     * Get rules for optionally given parameters.
     *
     * @param null|mixed[] $params
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\RuleInterface[]
     */
    public function getRules(?array $params = null): array;
}

\class_alias(
    RuleProviderInterface::class,
    'StepTheFkUp\EasyDecision\Interfaces\RuleProviderInterface',
    false
);
