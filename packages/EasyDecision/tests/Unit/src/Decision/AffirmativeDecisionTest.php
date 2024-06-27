<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Unit\Decision;

use EonX\EasyDecision\Decision\AffirmativeDecision;
use EonX\EasyDecision\Rule\RuleInterface;
use EonX\EasyDecision\Tests\Stub\Rule\OutputFromInputRuleStub;
use EonX\EasyDecision\Tests\Unit\AbstractUnitTestCase;

final class AffirmativeDecisionTest extends AbstractUnitTestCase
{
    public function testDecisionResetItsOutputOnEachMake(): void
    {
        $decision = (new AffirmativeDecision())->addRule(new OutputFromInputRuleStub());

        self::assertTrue($decision->make(['output' => true]));
        self::assertFalse($decision->make(['output' => false]));
    }

    public function testReturnFalseWhenNoTrue(): void
    {
        $decision = (new AffirmativeDecision())->addRules([
            $this->createFalseRule('false-1'),
            $this->createUnsupportedRule('unsupported-1'),
        ]);

        $expected = [
            'false-1' => false,
            'unsupported-1' => RuleInterface::OUTPUT_UNSUPPORTED,
        ];

        self::assertFalse($decision->make([]));
        self::assertEquals($expected, $decision->getContext()->getRuleOutputs());
    }

    public function testReturnTrueAtFirstTrue(): void
    {
        $decision = (new AffirmativeDecision())->addRules([
            $this->createTrueRule('true-1'),
            $this->createTrueRule('true-2'),
            $this->createTrueRule('true-3'),
            $this->createUnsupportedRule('unsupported-1'),
        ]);

        $expected = [
            'true-1' => true,
            'true-2' => RuleInterface::OUTPUT_SKIPPED,
            'true-3' => RuleInterface::OUTPUT_SKIPPED,
            'unsupported-1' => RuleInterface::OUTPUT_SKIPPED,
        ];

        self::assertTrue($decision->make([]));
        self::assertEquals($expected, $decision->getContext()->getRuleOutputs());
    }

    public function testReturnTrueInMiddleOfRules(): void
    {
        $decision = (new AffirmativeDecision())->addRules([
            $this->createFalseRule('false-1'),
            $this->createTrueRule('true-1'),
            $this->createTrueRule('true-2'),
            $this->createUnsupportedRule('unsupported-1'),
        ]);

        $expected = [
            'false-1' => false,
            'true-1' => true,
            'true-2' => RuleInterface::OUTPUT_SKIPPED,
            'unsupported-1' => RuleInterface::OUTPUT_SKIPPED,
        ];

        self::assertTrue($decision->make([]));
        self::assertEquals($expected, $decision->getContext()->getRuleOutputs());
    }

    public function testReturnTrueWithPriorities(): void
    {
        $decision = (new AffirmativeDecision())->addRules([
            $this->createFalseRule('false-1', 100),
            $this->createTrueRule('true-1'),
        ]);

        $expected = [
            'true-1' => true,
            'false-1' => RuleInterface::OUTPUT_SKIPPED,
        ];

        self::assertTrue($decision->make([]));
        self::assertEquals($expected, $decision->getContext()->getRuleOutputs());
    }
}
