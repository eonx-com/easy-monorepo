<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Tests\Stubs;

use StepTheFkUp\EasyDecision\Interfaces\RuleProviderInterface;
use StepTheFkUp\EasyDecision\Rules\ExpressionLanguageRule;

final class RuleProviderStub implements RuleProviderInterface
{
    /**
     * Get rules.
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\RuleInterface[]
     */
    public function getRules(): array
    {
        return [
            new RuleStub('true-1', true),
            new ExpressionLanguageRule('value === 1'),
            new ExpressionLanguageRule('value < 2')
        ];
    }
}

\class_alias(
    RuleProviderStub::class,
    'LoyaltyCorp\EasyDecision\Tests\Stubs\RuleProviderStub',
    false
);
