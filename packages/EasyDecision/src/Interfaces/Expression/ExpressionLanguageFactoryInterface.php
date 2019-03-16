<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces\Expression;

use StepTheFkUp\EasyDecision\Expression\ExpressionLanguage;

interface ExpressionLanguageFactoryInterface
{
    /**
     * Create expression language for given config.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\Expression\ExpressionLanguageConfigInterface $config
     *
     * @return \StepTheFkUp\EasyDecision\Expression\ExpressionLanguage
     */
    public function create(ExpressionLanguageConfigInterface $config): ExpressionLanguage;
}
