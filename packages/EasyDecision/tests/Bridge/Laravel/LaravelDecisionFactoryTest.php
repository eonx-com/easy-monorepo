<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Bridge\Laravel;

use EonX\EasyDecision\Decisions\AffirmativeDecision;
use EonX\EasyDecision\Exceptions\InvalidArgumentException;
use EonX\EasyDecision\Helpers\FromPhpExpressionFunctionProvider;
use EonX\EasyDecision\Tests\AbstractLumenTestCase;
use EonX\EasyDecision\Tests\Stubs\DecisionConfigProviderStub;
use EonX\EasyDecision\Tests\Stubs\RuleProviderStub;

final class LaravelDecisionFactoryTest extends AbstractLumenTestCase
{
    /**
     * Factory should create decision from array config successfully.
     *
     * @return void
     */
    public function testArrayConfigCreateDecisionSuccessfully(): void
    {
        $this->setConfig([
            'decisions' => [
                'my-decision' => [
                    'providers' => [new RuleProviderStub()],
                    'type' => AffirmativeDecision::class
                ]
            ]
        ]);

        self::assertInstanceOf(AffirmativeDecision::class, $this->getDecisionFactory()->create('my-decision'));
    }

    /**
     * Factory should throw exception if config array and providers empty.
     *
     * @return void
     */
    public function testArrayConfigEmptyProvidersException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->setConfig(['decisions' => ['my-decision' => ['providers' => '']]]);

        $this->getDecisionFactory()->create('my-decision');
    }

    /**
     * Factory should throw exception if config array and type empty.
     *
     * @return void
     */
    public function testArrayConfigEmptyType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->setConfig([
            'decisions' => [
                'my-decision' => [
                    'providers' => 'my-provider',
                    'type' => ''
                ]
            ]
        ]);

        $this->getDecisionFactory()->create('my-decision');
    }

    /**
     * Factory should create decision successfully and instantiate rule provider from container.
     *
     * @return void
     */
    public function testCreateDecisionSuccessfullyWithRuleProviderFromContainer(): void
    {
        $this->getApplication()->instance(RuleProviderStub::class, new RuleProviderStub());

        $this->setConfig([
            'decisions' => [
                'my-decision' => [
                    'providers' => RuleProviderStub::class,
                    'type' => AffirmativeDecision::class
                ]
            ]
        ]);

        self::assertInstanceOf(AffirmativeDecision::class, $this->getDecisionFactory()->create('my-decision'));
    }

    /**
     * Factory should cache resolved decisions and global expression functions.
     *
     * @return void
     */
    public function testCreateWithProviderConfigWithExpressionFunctionsSuccessfully(): void
    {
        $this->getApplication()->instance('minPhpFunctionProvider', new FromPhpExpressionFunctionProvider(['min']));

        $this->setConfig([
            'decisions' => [
                'my-decision' => new DecisionConfigProviderStub(),
                'my-decision-different' => new DecisionConfigProviderStub()
            ]
        ]);

        $decisionFactory = $this->getDecisionFactory();
        $decision = $decisionFactory->create('my-decision');
        $decisionAgain = $decisionFactory->create('my-decision');
        $differentDecision = $decisionFactory->create('my-decision-different');

        self::assertNotEquals(\spl_object_hash($decision), \spl_object_hash($decisionAgain));
        self::assertEquals($decision->make([]), $differentDecision->make([]));
    }

    /**
     * Factory should throw exception if decision config provider doesn't implement the right interface.
     *
     * @return void
     */
    public function testDecisionConfigProviderDoesntImplementInterfaceException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getApplication()->instance('MyDecisionConfigProvider', new \stdClass());

        $this->setConfig([
            'decisions' => [
                'my-decision' => 'MyDecisionConfigProvider'
            ]
        ]);

        $this->getDecisionFactory()->create('my-decision');
    }

    /**
     * Factory should throw exception if config isn't a string, array or instance of config provider interface.
     *
     * @return void
     */
    public function testInvalidConfigTypeException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->setConfig(['decisions' => ['my-decision' => 1]]);

        $this->getDecisionFactory()->create('my-decision');
    }

    /**
     * Factory should throw exception if no config for given decision.
     *
     * @return void
     */
    public function testNoConfigForDecisionException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getDecisionFactory()->create('invalid');
    }

    /**
     * Set config.
     *
     * @param mixed[] $config
     *
     * @return void
     */
    private function setConfig(array $config): void
    {
        $this->getApplication()->make('config')->set('easy-decision', $config);
    }
}
