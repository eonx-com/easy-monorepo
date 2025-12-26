<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Unit\Decision;

use EonX\EasyDecision\Decision\AffirmativeDecision;
use EonX\EasyDecision\Decision\ConsensusDecision;
use EonX\EasyDecision\Decision\UnanimousDecision;
use EonX\EasyDecision\Decision\ValueDecision;
use EonX\EasyDecision\Tests\Unit\AbstractUnitTestCase;

final class ConsensusDecisionTest extends AbstractUnitTestCase
{
    public function testGetName(): void
    {
        self::assertEquals('name', new ConsensusDecision('name')->getName());
    }

    public function testNoRulesDecision(): void
    {
        self::assertTrue(new AffirmativeDecision()->addRules([])->make([]));
        self::assertTrue(new ConsensusDecision()->addRules([])->make([]));
        self::assertTrue(new UnanimousDecision()->addRules([])->make([]));
        self::assertEquals(5, new ValueDecision()->addRules([])->make([
            'value' => 5,
        ]));
        self::assertEquals(10, new ValueDecision()->setDefaultOutput(10)->make([
            'value' => 5,
        ]));
    }

    public function testReturnFalseWhenMoreFalseThanTrue(): void
    {
        $decision = new ConsensusDecision()
->addRules([
            $this->createTrueRule('true-1'),
            $this->createFalseRule('false-1'),
            $this->createFalseRule('false-2'),
            $this->createUnsupportedRule('unsupported-1'),
        ]);

        $expected = [
            'true-1' => true,
            'false-1' => false,
            'false-2' => false,
            'unsupported-1' => 'unsupported',
        ];

        self::assertFalse($decision->make([]));
        self::assertEquals($expected, $decision->getContext()->getRuleOutputs());
    }

    public function testReturnTrueWhenMoreTrueThenFalse(): void
    {
        $decision = new ConsensusDecision()
->addRules([
            $this->createTrueRule('true-1'),
            $this->createTrueRule('true-2'),
            $this->createFalseRule('false-1'),
            $this->createUnsupportedRule('unsupported-1'),
        ]);

        $expected = [
            'true-1' => true,
            'true-2' => true,
            'false-1' => false,
            'unsupported-1' => 'unsupported',
        ];

        self::assertTrue($decision->make([]));
        self::assertEquals($expected, $decision->getContext()->getRuleOutputs());
    }

    public function testReturnTrueWhenNoRulesSupported(): void
    {
        $decision = new ConsensusDecision()
->addRules([$this->createUnsupportedRule('unsupported-1')]);

        $expected = [
            'unsupported-1' => 'unsupported',
        ];

        self::assertTrue($decision->make([]));
        self::assertEquals($expected, $decision->getContext()->getRuleOutputs());
    }

    public function testReturnTrueWhenSameNumberOfTrueAndFalse(): void
    {
        $decision = new ConsensusDecision()
->addRules([
            $this->createTrueRule('true-1'),
            $this->createTrueRule('true-2'),
            $this->createFalseRule('false-1'),
            $this->createFalseRule('false-2'),
            $this->createUnsupportedRule('unsupported-1'),
        ]);

        $expected = [
            'true-1' => true,
            'true-2' => true,
            'false-1' => false,
            'false-2' => false,
            'unsupported-1' => 'unsupported',
        ];

        self::assertTrue($decision->make([]));
        self::assertEquals($expected, $decision->getContext()->getRuleOutputs());
    }
}
