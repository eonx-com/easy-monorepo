<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Common\Interfaces;

use EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;

interface ExpressionLanguageConfigFactoryInterface
{
    /**
     * Create expression language config for given decision.
     *
     * @param string $decision
     *
     * @return null|\EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface
     */
    public function create(string $decision): ?ExpressionLanguageConfigInterface;
}
