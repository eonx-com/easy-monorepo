<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stub\Configurator;

use EonX\EasyDecision\Configurator\AbstractDecisionConfigurator;
use EonX\EasyDecision\Decision\DecisionInterface;
use EonX\EasyDecision\Rule\ExpressionLanguageRule;
use EonX\EasyDecision\Tests\Stub\Rule\RuleStub;

final class RulesConfiguratorStub extends AbstractDecisionConfigurator
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
