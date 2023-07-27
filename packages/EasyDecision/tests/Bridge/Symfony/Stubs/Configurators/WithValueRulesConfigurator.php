<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Bridge\Symfony\Stubs\Configurators;

use EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface;
use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Rules\ExpressionLanguageRule;

final class WithValueRulesConfigurator implements DecisionConfiguratorInterface
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
