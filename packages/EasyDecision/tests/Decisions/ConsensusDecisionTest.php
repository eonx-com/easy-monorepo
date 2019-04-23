<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Tests\Decisions;

use LoyaltyCorp\EasyDecision\Decisions\ConsensusDecision;
use LoyaltyCorp\EasyDecision\Exceptions\EmptyRulesException;
use LoyaltyCorp\EasyDecision\Interfaces\RuleInterface;
use LoyaltyCorp\EasyDecision\Tests\AbstractTestCase;

final class ConsensusDecisionTest extends AbstractTestCase
{
    /**
     * Decision should throw an exception if no rules given.
     *
     * @return void
     */
    public function testNoRulesException(): void
    {
        $this->expectException(EmptyRulesException::class);

        (new ConsensusDecision())->addRules([])->make([]);
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

\class_alias(
    ConsensusDecisionTest::class,
    'StepTheFkUp\EasyDecision\Tests\Decisions\ConsensusDecisionTest',
    false
);
