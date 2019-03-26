<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces;

interface RuleProviderInterface
{
    /**
     * Get rules.
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\RuleInterface[]
     */
    public function getRules(): array;
}
