<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Tests\Decisions;

use StepTheFkUp\EasyDecision\Decisions\DecisionConfig;
use StepTheFkUp\EasyDecision\Decisions\DecisionFactory;
use StepTheFkUp\EasyDecision\Decisions\UnanimousDecision;
use StepTheFkUp\EasyDecision\Exceptions\InvalidArgumentException;
use StepTheFkUp\EasyDecision\Exceptions\InvalidDecisionException;
use StepTheFkUp\EasyDecision\Expressions\ExpressionLanguageConfig;
use StepTheFkUp\EasyDecision\Interfaces\DecisionInterface;
use StepTheFkUp\EasyDecision\Interfaces\RuleProviderInterface;
use StepTheFkUp\EasyDecision\Tests\AbstractTestCase;
use StepTheFkUp\EasyDecision\Tests\Stubs\RuleStub;

final class DecisionFactoryTest extends AbstractTestCase
{
    /**
     * Factory should create expected decision with expected rules.
     *
     * @return void
     */
    public function testCreateDecisionSuccessfully(): void
    {
        $mapping = [DecisionInterface::TYPE_YESNO_UNANIMOUS => UnanimousDecision::class];
        $config = new DecisionConfig(
            DecisionInterface::TYPE_YESNO_UNANIMOUS,
            [$this->getRuleProvider()],
            new ExpressionLanguageConfig()
        );

        $decision = (new DecisionFactory($mapping, $this->getExpressionLanguageFactory()))->create($config);

        $expected = ['true-1' => true];

        self::assertTrue($decision->make([]));
        self::assertEquals(DecisionInterface::TYPE_YESNO_UNANIMOUS, $decision->getContext()->getDecisionType());
        self::assertEquals($expected, $decision->getContext()->getRuleOutputs());
    }

    /**
     * Factory should throw exception if instantiated decision does not implement DecisionInterface.
     *
     * @return void
     */
    public function testInvalidDecisionInMappingException(): void
    {
        $this->expectException(InvalidDecisionException::class);

        $mapping = ['decision' => \stdClass::class];
        $config = new DecisionConfig('decision', []);

        (new DecisionFactory($mapping, $this->getExpressionLanguageFactory()))->create($config);
    }

    /**
     * Factory should throw exception if given decision type isn't in mapping.
     *
     * @return void
     */
    public function testNotInMappingDecisionException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new DecisionFactory([], $this->getExpressionLanguageFactory()))->create(new DecisionConfig('', []));
    }

    /**
     * Get rule provider.
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\RuleProviderInterface
     */
    private function getRuleProvider(): RuleProviderInterface
    {
        return new class implements RuleProviderInterface
        {
            /**
             * Get rules.
             *
             * @return \StepTheFkUp\EasyDecision\Interfaces\RuleInterface[]
             */
            public function getRules(): array
            {
                return [
                    new RuleStub('true-1', true)
                ];
            }
        };
    }
}
