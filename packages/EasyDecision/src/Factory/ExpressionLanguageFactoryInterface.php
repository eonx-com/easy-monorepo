<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Factory;

use EonX\EasyDecision\ExpressionLanguage\ExpressionLanguageInterface;

interface ExpressionLanguageFactoryInterface
{
    public function create(): ExpressionLanguageInterface;
}
