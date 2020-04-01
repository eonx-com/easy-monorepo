<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Expressions\Interfaces;

interface ExpressionLanguageFactoryInterface
{
    public function create(?ExpressionLanguageConfigInterface $config = null): ExpressionLanguageInterface;
}
