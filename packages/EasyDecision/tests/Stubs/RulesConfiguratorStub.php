<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stubs;

use EonX\EasyDecision\Configurators\AbstractConfigurator;
use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Rules\ExpressionLanguageRule;

final class RulesConfiguratorStub extends AbstractConfigurator
{
    public function configure(DecisionInterface $decision): void
    {
        $decision->addRules([
            new RuleStub('true-1', true),
            new ExpressionLanguageRule('value === 1'),
            new ExpressionLanguageRule('value < 2'),
        ]);
    }
}
