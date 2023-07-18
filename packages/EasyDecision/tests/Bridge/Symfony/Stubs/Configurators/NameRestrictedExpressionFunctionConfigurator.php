<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Bridge\Symfony\Stubs\Configurators;

use EonX\EasyDecision\Configurators\AbstractNameRestrictedConfigurator;
use EonX\EasyDecision\Expressions\ExpressionFunction;
use EonX\EasyDecision\Interfaces\DecisionInterface;

final class NameRestrictedExpressionFunctionConfigurator extends AbstractNameRestrictedConfigurator
{
    public function configure(DecisionInterface $decision): void
    {
        $expressionLanguage = $decision->getExpressionLanguage();

        if ($expressionLanguage === null) {
            return;
        }

        $expressionLanguage->addFunction(new ExpressionFunction('restricted', function (): void {
        }));
    }

    protected function getNames(): array
    {
        return ['restricted'];
    }
}
