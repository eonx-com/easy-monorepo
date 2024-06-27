<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stub\Configurator;

use EonX\EasyDecision\Configurator\AbstractNameRestrictedDecisionConfigurator;
use EonX\EasyDecision\Decision\DecisionInterface;
use EonX\EasyDecision\ExpressionFunction\ExpressionFunction;

final class NameRestrictedExpressionFunctionDecisionConfigurator extends AbstractNameRestrictedDecisionConfigurator
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
