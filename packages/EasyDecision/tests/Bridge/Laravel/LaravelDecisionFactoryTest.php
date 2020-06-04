<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Bridge\Laravel;

use EonX\EasyDecision\Decisions\AffirmativeDecision;
use EonX\EasyDecision\Decisions\ValueDecision;
use EonX\EasyDecision\Exceptions\InvalidArgumentException;
use EonX\EasyDecision\Exceptions\InvalidMappingException;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageInterface;
use EonX\EasyDecision\Helpers\FromPhpExpressionFunctionProvider;
use EonX\EasyDecision\Interfaces\DecisionFactoryInterface;
use EonX\EasyDecision\Tests\AbstractLumenTestCase;
use EonX\EasyDecision\Tests\Stubs\DecisionConfigProviderStub;
use EonX\EasyDecision\Tests\Stubs\RuleProviderStub;

final class LaravelDecisionFactoryTest extends AbstractLumenTestCase
{
    public function testArrayConfigCreateDecisionSuccessfully(): void
    {
        $this->setConfig([
            'decisions' => [
                'my-decision' => [
                    'providers' => [new RuleProviderStub()],
                    'type' => AffirmativeDecision::class,
                ],
            ],
        ]);

        self::assertInstanceOf(AffirmativeDecision::class, $this->getDecisionFactory()->create('my-decision'));
    }

    public function testArrayConfigEmptyProvidersException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->setConfig(['decisions' => ['my-decision' => ['providers' => '']]]);

        $this->getDecisionFactory()->create('my-decision');
    }

    public function testArrayConfigEmptyType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->setConfig([
            'decisions' => [
                'my-decision' => [
                    'providers' => 'my-provider',
                    'type' => '',
                ],
            ],
        ]);

        $this->getDecisionFactory()->create('my-decision');
    }

    public function testCreateDecisionByNameThrowsInvalidMappingException(): void
    {
        $this->expectException(InvalidMappingException::class);
        $this->expectExceptionMessage('The "some-decision" decision type is not configured');
        $factory = $this->getApplication()->get(DecisionFactoryInterface::class);

        $factory->createByName('some-decision');
    }

    public function testCreateDecisionByNameSucceeds(): void
    {
        $this->setConfig([
            'type_mapping' => [
                'some-decision' => ValueDecision::class,
            ],
        ]);
        $factory = $this->getApplication()->get(DecisionFactoryInterface::class);

        $decision = $factory->createByName('some-decision');

        self::assertInstanceOf(ValueDecision::class, $decision);
    }

    public function testCreateDecisionSuccessfullyWithRuleProviderFromContainer(): void
    {
        $this->getApplication()->instance(RuleProviderStub::class, new RuleProviderStub());

        $this->setConfig([
            'decisions' => [
                'my-decision' => [
                    'providers' => RuleProviderStub::class,
                    'type' => AffirmativeDecision::class,
                ],
            ],
        ]);

        self::assertInstanceOf(AffirmativeDecision::class, $this->getDecisionFactory()->create('my-decision'));
    }

    public function testCreateDecisionWithConfigurators(): void
    {
        $factory = $this->getApplication()->get(DecisionFactoryInterface::class);
        $decision = $factory->createValueDecision();

        self::assertInstanceOf(ValueDecision::class, $decision);
        self::assertInstanceOf(ExpressionLanguageInterface::class, $decision->getExpressionLanguage());
    }

    public function testCreateWithProviderConfigWithExpressionFunctionsSuccessfully(): void
    {
        $this->getApplication()->instance('minPhpFunctionProvider', new FromPhpExpressionFunctionProvider(['min']));

        $this->setConfig([
            'decisions' => [
                'my-decision' => new DecisionConfigProviderStub(),
                'my-decision-different' => new DecisionConfigProviderStub(),
            ],
        ]);

        $decisionFactory = $this->getDecisionFactory();
        $decision = $decisionFactory->create('my-decision');
        $decisionAgain = $decisionFactory->create('my-decision');
        $differentDecision = $decisionFactory->create('my-decision-different');

        self::assertNotSame(\spl_object_hash($decision), \spl_object_hash($decisionAgain));
        self::assertEquals($decision->make([]), $differentDecision->make([]));
    }

    public function testDecisionConfigProviderDoesntImplementInterfaceException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getApplication()->instance('MyDecisionConfigProvider', new \stdClass());

        $this->setConfig([
            'decisions' => [
                'my-decision' => 'MyDecisionConfigProvider',
            ],
        ]);

        $this->getDecisionFactory()->create('my-decision');
    }

    public function testInvalidConfigTypeException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->setConfig(['decisions' => ['my-decision' => 1]]);

        $this->getDecisionFactory()->create('my-decision');
    }

    public function testNoConfigForDecisionException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getDecisionFactory()->create('invalid');
    }

    /**
     * @param mixed[] $config
     */
    private function setConfig(array $config): void
    {
        $this->getApplication()->make('config')->set('easy-decision', $config);
    }
}
