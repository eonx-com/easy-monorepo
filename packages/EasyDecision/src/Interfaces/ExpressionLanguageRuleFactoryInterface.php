<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

use EonX\EasyDecision\Rules\ExpressionLanguageRule;

interface ExpressionLanguageRuleFactoryInterface
{
    /**
     * Create expression language rule for given expression and priority.
     *
     * @param string $expression
     * @param null|int $priority
     * @param null|string $name
     * @param null|mixed[] $extra
     *
     * @return \EonX\EasyDecision\Rules\ExpressionLanguageRule
     */
    public function create(
        string $expression,
        ?int $priority = null,
        ?string $name = null,
        ?array $extra = null
    ): ExpressionLanguageRule;
}
