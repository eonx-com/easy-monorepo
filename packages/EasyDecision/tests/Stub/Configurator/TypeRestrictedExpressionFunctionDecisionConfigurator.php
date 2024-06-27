<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stub\Configurator;

use EonX\EasyDecision\Configurator\AbstractTypeRestrictedDecisionConfigurator;
use EonX\EasyDecision\Decision\DecisionInterface;
use EonX\EasyDecision\Decision\ValueDecision;
use EonX\EasyDecision\ExpressionFunction\ExpressionFunction;

final class TypeRestrictedExpressionFunctionDecisionConfigurator extends AbstractTypeRestrictedDecisionConfigurator
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

    protected function getType(): string
    {
        return ValueDecision::class;
    }
}
