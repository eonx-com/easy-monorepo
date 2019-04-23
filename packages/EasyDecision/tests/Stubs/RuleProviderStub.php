<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Tests\Stubs;

use LoyaltyCorp\EasyDecision\Interfaces\RuleProviderInterface;
use LoyaltyCorp\EasyDecision\Rules\ExpressionLanguageRule;

final class RuleProviderStub implements RuleProviderInterface
{
    /**
     * Get rules.
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\RuleInterface[]
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
    'StepTheFkUp\EasyDecision\Tests\Stubs\RuleProviderStub',
    false
);
