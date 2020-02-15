<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Decisions;

use EonX\EasyDecision\Decisions\AffirmativeDecision;
use EonX\EasyDecision\Decisions\ConsensusDecision;
use EonX\EasyDecision\Decisions\UnanimousDecision;
use EonX\EasyDecision\Decisions\ValueDecision;
use EonX\EasyDecision\Interfaces\RuleInterface;
use EonX\EasyDecision\Tests\AbstractTestCase;

final class ConsensusDecisionTest extends AbstractTestCase
{
    /**
     * Decision should return expected name.
     *
     * @return void
     */
    public function testGetName(): void
    {
        self::assertEquals('name', (new ConsensusDecision('name'))->getName());
    }

    /**
     * Decision should return expected default output if no rules given.
     *
     * @return void
     */
    public function testNoRulesDecision(): void
    {
        self::assertTrue((new AffirmativeDecision())->addRules([])->make([]));
        self::assertTrue((new ConsensusDecision())->addRules([])->make([]));
        self::assertTrue((new UnanimousDecision())->addRules([])->make([]));
        self::assertEquals(5, (new ValueDecision())->addRules([])->make(['value' => 5]));
        self::assertEquals(10, (new ValueDecision())->setDefaultOutput(10)->make(['value' => 5]));
    }

    /**
     * Decision should return false when more false than true.
     *
     * @return void
     */
    public function testReturnFalseWhenMoreFalseThanTrue(): void
    {
        $decision = (new ConsensusDecision())->addRules([
            $this->createTrueRule('true-1'),
            $this->createFalseRule('false-1'),
            $this->createFalseRule('false-2'),
            $this->createUnsupportedRule('unsupported-1')
        ]);

        $expected = [
            'true-1' => true,
            'false-1' => false,
            'false-2' => false,
            'unsupported-1' => RuleInterface::OUTPUT_UNSUPPORTED
        ];

        self::assertFalse($decision->make([]));
        self::assertEquals($expected, $decision->getContext()->getRuleOutputs());
    }

    /**
     * Decision should return true when more true than false.
     *
     * @return void
     */
    public function testReturnTrueWhenMoreTrueThenFalse(): void
    {
        $decision = (new ConsensusDecision())->addRules([
            $this->createTrueRule('true-1'),
            $this->createTrueRule('true-2'),
            $this->createFalseRule('false-1'),
            $this->createUnsupportedRule('unsupported-1')
        ]);

        $expected = [
            'true-1' => true,
            'true-2' => true,
            'false-1' => false,
            'unsupported-1' => RuleInterface::OUTPUT_UNSUPPORTED
        ];

        self::assertTrue($decision->make([]));
        self::assertEquals($expected, $decision->getContext()->getRuleOutputs());
    }

    /**
     * Decision should return true when no supported rules.
     *
     * @return void
     */
    public function testReturnTrueWhenNoRulesSupported(): void
    {
        $decision = (new ConsensusDecision())->addRules([$this->createUnsupportedRule('unsupported-1')]);

        $expected = ['unsupported-1' => RuleInterface::OUTPUT_UNSUPPORTED];

        self::assertTrue($decision->make([]));
        self::assertEquals($expected, $decision->getContext()->getRuleOutputs());
    }

    /**
     * Decision should return true when same number of true and false.
     *
     * @return void
     */
    public function testReturnTrueWhenSameNumberOfTrueAndFalse(): void
    {
        $decision = (new ConsensusDecision())->addRules([
            $this->createTrueRule('true-1'),
            $this->createTrueRule('true-2'),
            $this->createFalseRule('false-1'),
            $this->createFalseRule('false-2'),
            $this->createUnsupportedRule('unsupported-1')
        ]);

        $expected = [
            'true-1' => true,
            'true-2' => true,
            'false-1' => false,
            'false-2' => false,
            'unsupported-1' => RuleInterface::OUTPUT_UNSUPPORTED
        ];

        self::assertTrue($decision->make([]));
        self::assertEquals($expected, $decision->getContext()->getRuleOutputs());
    }
}
