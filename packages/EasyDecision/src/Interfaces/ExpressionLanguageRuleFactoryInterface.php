<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

use EonX\EasyDecision\Rules\ExpressionLanguageRule;

interface ExpressionLanguageRuleFactoryInterface
{
    /**
     * @param null|mixed[] $extra
     */
    public function create(
        string $expression,
        ?int $priority = null,
        ?string $name = null,
        ?array $extra = null,
    ): ExpressionLanguageRule;
}
