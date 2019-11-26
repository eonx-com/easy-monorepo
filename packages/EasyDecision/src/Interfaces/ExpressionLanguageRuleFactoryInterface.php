<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces;

use LoyaltyCorp\EasyDecision\Rules\ExpressionLanguageRule;

interface ExpressionLanguageRuleFactoryInterface
{
    /**
     * Create expression language rule for given expression and priority.
     *
     * @param string $expression
     * @param null|int $priority
     *
     * @return \LoyaltyCorp\EasyDecision\Rules\ExpressionLanguageRule
     */
    public function create(string $expression, ?int $priority = null): ExpressionLanguageRule;
}


