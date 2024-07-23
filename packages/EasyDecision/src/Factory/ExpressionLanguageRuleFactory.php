<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Factory;

use EonX\EasyDecision\Rule\ExpressionLanguageRule;

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
