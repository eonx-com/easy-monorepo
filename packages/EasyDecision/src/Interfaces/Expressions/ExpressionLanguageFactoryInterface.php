<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces\Expressions;

use EonX\EasyDecision\Expressions\ExpressionLanguage;

interface ExpressionLanguageFactoryInterface
{
    /**
     * Create expression language for given config.
     *
     * @param \EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface $config
     *
     * @return \EonX\EasyDecision\Expressions\ExpressionLanguage
     */
    public function create(ExpressionLanguageConfigInterface $config): ExpressionLanguage;
}
