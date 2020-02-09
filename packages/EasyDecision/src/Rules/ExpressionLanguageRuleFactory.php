<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Rules;

use EonX\EasyDecision\Interfaces\ExpressionLanguageRuleFactoryInterface;

final class ExpressionLanguageRuleFactory implements ExpressionLanguageRuleFactoryInterface
{
    /**
     * Create expression language rule for given expression and priority.
     *
     * @param string $expression
     * @param null|int $priority
     * @param null|string $name
     *
     * @return \EonX\EasyDecision\Rules\ExpressionLanguageRule
     */
    public function create(string $expression, ?int $priority = null, ?string $name = null): ExpressionLanguageRule
    {
        return new ExpressionLanguageRule($expression, $priority, $name);
    }
}
