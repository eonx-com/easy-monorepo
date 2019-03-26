<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Tests\Decisions;

use StepTheFkUp\EasyDecision\Decisions\UnanimousDecision;
use StepTheFkUp\EasyDecision\Interfaces\RuleInterface;
use StepTheFkUp\EasyDecision\Tests\AbstractTestCase;

final class UnanimousDecisionTest extends AbstractTestCase
{
    /**
     * Decision should return false when at least one false.
     *
     * @return void
     */
    public function testReturnFalseWhenAtLeastOneFalse(): void
    {
        $decision = (new UnanimousDecision())->addRules([
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

        self::assertFalse($decision->make([]));
        self::assertEquals($expected, $decision->getContext()->getRuleOutputs());
    }

    /**
     * Decision should return when only trues.
     *
     * @return void
     */
    public function testReturnTrueIfOnlyTrues(): void
    {
        $decision = (new UnanimousDecision())->addRules([
            $this->createTrueRule('true-1'),
            $this->createTrueRule('true-2'),
            $this->createTrueRule('true-3'),
            $this->createUnsupportedRule('unsupported-1')
        ]);

        $expected = [
            'true-1' => true,
            'true-2' => true,
            'true-3' => true,
            'unsupported-1' => RuleInterface::OUTPUT_UNSUPPORTED
        ];

        self::assertTrue($decision->make([]));
        self::assertEquals($expected, $decision->getContext()->getRuleOutputs());
    }

    /**
     * Decision should return true when only unsupported.
     *
     * @return void
     */
    public function testReturnTrueWhenOnlyUnsupported(): void
    {
        $decision = (new UnanimousDecision())->addRules([
            $this->createUnsupportedRule('unsupported-1')
        ]);

        $expected = [
            'unsupported-1' => RuleInterface::OUTPUT_UNSUPPORTED
        ];

        self::assertTrue($decision->make([]));
        self::assertEquals($expected, $decision->getContext()->getRuleOutputs());
    }
}
