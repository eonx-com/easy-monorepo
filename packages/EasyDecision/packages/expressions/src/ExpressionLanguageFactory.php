<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Expressions;

use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageFactoryInterface;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageInterface;

final class ExpressionLanguageFactory implements ExpressionLanguageFactoryInterface
{
    public function create(): ExpressionLanguageInterface
    {
        return new ExpressionLanguage();
    }
}
