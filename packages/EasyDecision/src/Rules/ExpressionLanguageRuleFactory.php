<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Rules;

use StepTheFkUp\EasyDecision\Interfaces\ExpressionLanguageRuleFactoryInterface;

final class ExpressionLanguageRuleFactory implements ExpressionLanguageRuleFactoryInterface
{
    /**
     * Create expression language rule for given expression and priority.
     *
     * @param string $expression
     * @param null|int $priority
     *
     * @return \StepTheFkUp\EasyDecision\Rules\ExpressionLanguageRule
     */
    public function create(string $expression, ?int $priority = null): ExpressionLanguageRule
    {
        return new ExpressionLanguageRule($expression, $priority);
    }
}
