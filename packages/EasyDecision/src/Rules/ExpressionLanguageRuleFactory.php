<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Rules;

use EonX\EasyDecision\Interfaces\ExpressionLanguageRuleFactoryInterface;

final class ExpressionLanguageRuleFactory implements ExpressionLanguageRuleFactoryInterface
{
    public function create(
        string $expression,
        ?int $priority = null,
        ?string $name = null,
        ?array $extra = null,
    ): ExpressionLanguageRule {
        return new ExpressionLanguageRule($expression, $priority, $name, $extra);
    }
}
