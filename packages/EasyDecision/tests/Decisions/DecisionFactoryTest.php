<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Decisions;

use EonX\EasyDecision\Decisions\DecisionConfig;
use EonX\EasyDecision\Decisions\DecisionFactory;
use EonX\EasyDecision\Decisions\UnanimousDecision;
use EonX\EasyDecision\Exceptions\InvalidDecisionException;
use EonX\EasyDecision\Exceptions\InvalidRuleProviderException;
use EonX\EasyDecision\Expressions\ExpressionLanguageConfig;
use EonX\EasyDecision\Providers\ConfigMappingProvider;
use EonX\EasyDecision\Tests\AbstractTestCase;
use EonX\EasyDecision\Tests\Stubs\RuleProviderStub;

final class DecisionFactoryTest extends AbstractTestCase
{
    public function testCreateDecisionSuccessfully(): void
    {
        $config = new DecisionConfig(
            UnanimousDecision::class,
            'my-decision',
            [new RuleProviderStub()],
            new ExpressionLanguageConfig()
        );
        $mappingProvider = new ConfigMappingProvider([]);
        $decision = (new DecisionFactory($mappingProvider, $this->getExpressionLanguageFactory()))->create($config);

        $expected = [
            'true-1' => true,
            'value === 1' => true,
            'value < 2' => true,
        ];

        self::assertTrue($decision->make(['value' => 1]));
        self::assertSame(UnanimousDecision::class, $decision->getContext()->getDecisionType());
        self::assertEquals($expected, $decision->getContext()->getRuleOutputs());
    }

    public function testInvalidDecisionInMappingException(): void
    {
        $this->expectException(InvalidDecisionException::class);

        $config = new DecisionConfig(\stdClass::class, 'my-decision', []);
        $mappingProvider = new ConfigMappingProvider([]);

        (new DecisionFactory($mappingProvider, $this->getExpressionLanguageFactory()))->create($config);
    }

    public function testInvalidRuleProviderException(): void
    {
        $this->expectException(InvalidRuleProviderException::class);

        $mappingProvider = new ConfigMappingProvider([]);
        $config = new DecisionConfig(UnanimousDecision::class, 'my-decision', [new \stdClass()]);

        (new DecisionFactory($mappingProvider, $this->getExpressionLanguageFactory()))->create($config);
    }

    public function testNotInMappingDecisionException(): void
    {
        $this->expectException(InvalidDecisionException::class);

        $mappingProvider = new ConfigMappingProvider([]);

        (new DecisionFactory($mappingProvider, $this->getExpressionLanguageFactory()))->create(new DecisionConfig(
            '',
            'my-decision',
            []
        ));
    }
}
