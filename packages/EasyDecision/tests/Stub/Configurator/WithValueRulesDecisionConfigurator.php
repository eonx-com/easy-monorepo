<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stub\Configurator;

use EonX\EasyDecision\Configurator\DecisionConfiguratorInterface;
use EonX\EasyDecision\Decision\DecisionInterface;
use EonX\EasyDecision\Rule\ExpressionLanguageRule;

final class WithValueRulesDecisionConfigurator implements DecisionConfiguratorInterface
{
    public function configure(DecisionInterface $decision): void
    {
        $decision->addRule(new ExpressionLanguageRule('value + 10'));
    }

    public function getPriority(): int
    {
        return 0;
    }
}
