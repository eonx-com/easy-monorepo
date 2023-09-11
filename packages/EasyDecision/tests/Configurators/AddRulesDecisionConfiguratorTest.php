<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Configurators;

use EonX\EasyDecision\Configurators\AddRulesDecisionConfigurator;
use EonX\EasyDecision\Tests\AbstractTestCase;
use EonX\EasyDecision\Tests\Stubs\DecisionStub;
use EonX\EasyDecision\Tests\Stubs\RestrictedRuleStub;
use EonX\EasyDecision\Tests\Stubs\RuleStub;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AddRulesDecisionConfigurator::class)]
final class AddRulesDecisionConfiguratorTest extends AbstractTestCase
{
    public function testAddNonRestrictedRule(): void
    {
        $expectedRule = 'any-rule';
        $expectedOutput = 'my-output';

        $rules = [new RuleStub($expectedRule, $expectedOutput)];

        $configurator = new AddRulesDecisionConfigurator($rules);

        $decision = new DecisionStub('decision-stub');

        $configurator->configure($decision);

        $decision->make(['my-input']);

        $ruleOutputs = $decision->getContext()
            ->getRuleOutputs();

        self::assertArrayHasKey($expectedRule, $ruleOutputs);
        self::assertEquals($expectedOutput, $ruleOutputs[$expectedRule]);
    }

    public function testAddRestrictedRule(): void
    {
        $expectedRule = 'restricted-rule';
        $expectedOutput = 'my-output';

        $rules = [
            new RestrictedRuleStub($expectedRule, 'decision-stub', $expectedOutput),
            new RestrictedRuleStub('except-rule', 'any-decision', 'any-output'),
        ];

        $configurator = new AddRulesDecisionConfigurator($rules);

        $decision = new DecisionStub('decision-stub');

        $configurator->configure($decision);

        $decision->make(['my-input']);

        $ruleOutputs = $decision->getContext()
            ->getRuleOutputs();

        self::assertCount(1, $ruleOutputs);
        self::assertArrayNotHasKey('except-rule', $ruleOutputs);

        self::assertArrayHasKey($expectedRule, $ruleOutputs);
        self::assertEquals($expectedOutput, $ruleOutputs[$expectedRule]);
    }

    public function testFilterRules(): void
    {
        $expectedRule = 'restricted-rule';
        $expectedOutput = 'my-output';

        $rules = [
            new RestrictedRuleStub($expectedRule, 'decision-stub', $expectedOutput),
            new class() {
                // No body needed
            },
        ];

        $configurator = new AddRulesDecisionConfigurator($rules);

        $decision = new DecisionStub('decision-stub');

        $configurator->configure($decision);

        $decision->make(['my-input']);

        $ruleOutputs = $decision->getContext()
            ->getRuleOutputs();

        self::assertCount(1, $ruleOutputs);
        self::assertArrayNotHasKey('except-rule', $ruleOutputs);

        self::assertArrayHasKey($expectedRule, $ruleOutputs);
        self::assertEquals($expectedOutput, $ruleOutputs[$expectedRule]);
    }
}
