<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Rules;

use LoyaltyCorp\EasyDecision\Interfaces\ExpressionLanguageRuleFactoryInterface;

final class ExpressionLanguageRuleFactory implements ExpressionLanguageRuleFactoryInterface
{
    /**
     * Create expression language rule for given expression and priority.
     *
     * @param string $expression
     * @param null|int $priority
     *
     * @return \LoyaltyCorp\EasyDecision\Rules\ExpressionLanguageRule
     */
    public function create(string $expression, ?int $priority = null): ExpressionLanguageRule
    {
        return new ExpressionLanguageRule($expression, $priority);
    }
}

\class_alias(
    ExpressionLanguageRuleFactory::class,
    'StepTheFkUp\EasyDecision\Rules\ExpressionLanguageRuleFactory',
    false
);
