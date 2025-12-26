<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Unit\Factory;

use ArrayIterator;
use EonX\EasyDecision\Configurator\SetExpressionLanguageDecisionConfigurator;
use EonX\EasyDecision\Decision\UnanimousDecision;
use EonX\EasyDecision\Exception\InvalidMappingException;
use EonX\EasyDecision\Factory\DecisionFactory;
use EonX\EasyDecision\Factory\ExpressionLanguageFactory;
use EonX\EasyDecision\Provider\ConfigMappingProvider;
use EonX\EasyDecision\Tests\Stub\Configurator\DecisionConfiguratorStub;
use EonX\EasyDecision\Tests\Stub\Configurator\RulesConfiguratorStub;
use EonX\EasyDecision\Tests\Unit\AbstractUnitTestCase;

final class DecisionFactoryTest extends AbstractUnitTestCase
{
    public function testCreateDecisionSuccessfully(): void
    {
        $configurators = [
            new RulesConfiguratorStub(),
            new SetExpressionLanguageDecisionConfigurator(new ExpressionLanguageFactory()),
        ];
        $mappingProvider = new ConfigMappingProvider([]);
        $decision = new DecisionFactory($mappingProvider, $configurators)
->createUnanimousDecision();

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
        $exprLanguageConfigurator = new SetExpressionLanguageDecisionConfigurator(new ExpressionLanguageFactory());

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

        new DecisionFactory(new ConfigMappingProvider([]))->createByName('my-decision');
    }
}
