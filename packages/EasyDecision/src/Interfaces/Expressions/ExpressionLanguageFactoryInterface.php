<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces\Expression;

use StepTheFkUp\EasyDecision\Expressions\ExpressionLanguage;

interface ExpressionLanguageFactoryInterface
{
    /**
     * Create expression language for given config.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\Expression\ExpressionLanguageConfigInterface $config
     *
     * @return \StepTheFkUp\EasyDecision\Expressions\ExpressionLanguage
     */
    public function create(ExpressionLanguageConfigInterface $config): ExpressionLanguage;
}
