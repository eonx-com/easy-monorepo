<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Decisions;

use ArrayIterator;
use EonX\EasyDecision\Configurators\SetExpressionLanguageConfigurator;
use EonX\EasyDecision\Decisions\DecisionFactory;
use EonX\EasyDecision\Decisions\UnanimousDecision;
use EonX\EasyDecision\Exceptions\InvalidMappingException;
use EonX\EasyDecision\Expressions\ExpressionLanguageFactory;
use EonX\EasyDecision\Providers\ConfigMappingProvider;
use EonX\EasyDecision\Tests\AbstractTestCase;
use EonX\EasyDecision\Tests\Stubs\DecisionConfiguratorStub;
use EonX\EasyDecision\Tests\Stubs\RulesConfiguratorStub;

final class DecisionFactoryTest extends AbstractTestCase
{
    public function testCreateDecisionSuccessfully(): void
    {
        $configurators = [
            new RulesConfiguratorStub(),
            new SetExpressionLanguageConfigurator(new ExpressionLanguageFactory()),
        ];
        $mappingProvider = new ConfigMappingProvider([]);
        $decision = (new DecisionFactory($mappingProvider, $configurators))->createUnanimousDecision();

        $expected = [
            'true-1' => true,
            'value === 1' => true,
            'value < 2' => true,
        ];

        self::assertTrue($decision->make([
            'value' => 1,
        ]));
        self::assertSame(UnanimousDecision::class, $decision->getContext()->getDecisionType());
        self::assertEquals($expected, $decision->getContext()->getRuleOutputs());
    }

    public function testGetConfiguredDecisions(): void
    {
        $mappingProvider = new ConfigMappingProvider([]);

        $configurator1 = new DecisionConfiguratorStub('my-unanimous-decision');
        $configurator2 = new DecisionConfiguratorStub('my-consensus-decision');
        $exprLanguageConfigurator = new SetExpressionLanguageConfigurator(new ExpressionLanguageFactory());

        $decisionFactory = (new DecisionFactory(
            $mappingProvider,
            new ArrayIterator([$configurator1, $configurator2, $exprLanguageConfigurator])
        ));

        $decision1 = $decisionFactory->createUnanimousDecision('my-unanimous-decision');
        $decision2 = $decisionFactory->createConsensusDecision('my-consensus-decision');

        $configuredDecisions = $decisionFactory->getConfiguredDecisions();

        self::assertCount(2, $configuredDecisions);

        self::assertSame($configurator1, $decisionFactory->getConfiguratorsByDecision($decision1)[0]);
        self::assertSame($configurator2, $decisionFactory->getConfiguratorsByDecision($decision2)[0]);
    }

    public function testInvalidDecisionInMappingException(): void
    {
        $this->expectException(InvalidMappingException::class);

        (new DecisionFactory(new ConfigMappingProvider([])))->createByName('my-decision');
    }
}
