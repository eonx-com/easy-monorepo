<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Bridge\Laravel;

use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;

interface ExpressionLanguageConfigFactoryInterface
{
    /**
     * Create expression language config for given decision.
     *
     * @param string $decision
     *
     * @return null|\StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface
     */
    public function create(string $decision): ?ExpressionLanguageConfigInterface;
}
