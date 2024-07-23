<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Factory;

use EonX\EasyDecision\ExpressionLanguage\ExpressionLanguage;
use EonX\EasyDecision\ExpressionLanguage\ExpressionLanguageInterface;

final class ExpressionLanguageFactory implements ExpressionLanguageFactoryInterface
{
    public function create(): ExpressionLanguageInterface
    {
        return new ExpressionLanguage();
    }
}
