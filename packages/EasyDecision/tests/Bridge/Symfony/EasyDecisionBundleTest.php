<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Bridge\Symfony;

use EonX\EasyDecision\Decisions\AffirmativeDecision;
use EonX\EasyDecision\Decisions\ConsensusDecision;
use EonX\EasyDecision\Decisions\UnanimousDecision;
use EonX\EasyDecision\Decisions\ValueDecision;
use EonX\EasyDecision\Exceptions\UnableToMakeDecisionException;
use EonX\EasyDecision\Interfaces\DecisionFactoryInterface;
use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Tests\AbstractTestCase;
use EonX\EasyDecision\Tests\Bridge\Symfony\Stubs\KernelStub;

final class EasyDecisionBundleTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerCreateDecision(): iterable
    {
        yield 'Affirmative decision with no configurators' => [
            $this->getCreateAffirmativeDecision(),
            static function (DecisionInterface $decision): void {
                self::assertInstanceOf(AffirmativeDecision::class, $decision);
                self::assertEquals('<no-name>', $decision->getName());
            },
        ];

        yield 'Consensus decision with no configurators' => [
            $this->getCreateConsensusDecision(),
            static function (DecisionInterface $decision): void {
                self::assertInstanceOf(ConsensusDecision::class, $decision);
                self::assertEquals('<no-name>', $decision->getName());
            },
        ];

        yield 'Unanimous decision with no configurators' => [
            $this->getCreateUnanimousDecision(),
            static function (DecisionInterface $decision): void {
                self::assertInstanceOf(UnanimousDecision::class, $decision);
                self::assertEquals('<no-name>', $decision->getName());
            },
        ];

        yield 'Value decision with no configurators' => [
            $this->getCreateValueDecision(),
            static function (DecisionInterface $decision): void {
                self::assertInstanceOf(ValueDecision::class, $decision);
                self::assertEquals('<no-name>', $decision->getName());
                self::assertEquals(1, $decision->make(['value' => 1]));
            },
        ];

        yield 'Value decision with name configurator' => [
            $this->getCreateValueDecision(),
            static function (DecisionInterface $decision): void {
                self::assertInstanceOf(ValueDecision::class, $decision);
                self::assertEquals('my-value-decision', $decision->getName());
                self::assertEquals(1, $decision->make(['value' => 1]));
            },
            [__DIR__ . '/Fixtures/value_with_name.yaml'],
        ];

        yield 'Value decision with rules configurator' => [
            $this->getCreateValueDecision(),
            static function (DecisionInterface $decision): void {
                self::assertInstanceOf(ValueDecision::class, $decision);
                self::assertEquals(11, $decision->make(['value' => 1]));
            },
            [__DIR__ . '/Fixtures/value_with_rules_and_expression_language.yaml'],
        ];
    }

    /**
     * @param null|string[] $configPaths
     *
     * @dataProvider providerCreateDecision
     */
    public function testDecisions(callable $create, callable $assert, ?array $configPaths = null): void
    {
        $kernel = new KernelStub($configPaths);
        $kernel->boot();

        $factory = $kernel->getContainer()->get(DecisionFactoryInterface::class);
        $decision = \call_user_func($create, $factory);

        \call_user_func($assert, $decision);
    }

    public function testNotExpressionLanguageException(): void
    {
        $this->expectException(UnableToMakeDecisionException::class);
        $this->expectExceptionMessage('Decision "<no-name>" of type "EonX\EasyDecision\Decisions\ValueDecision": Expression language not set, to use it in your rules you must set it on the decision instance');

        $kernel = new KernelStub([
            __DIR__ . '/Fixtures/disable_expression_language.yaml',
            __DIR__ . '/Fixtures/value_with_rules_and_expression_language.yaml',
        ]);

        $kernel->boot();

        $factory = $kernel->getContainer()->get(DecisionFactoryInterface::class);
        $factory->createValueDecision()->make(['value' => 1]);
    }

    private function getCreateAffirmativeDecision(?string $name = null): \Closure
    {
        return static function (DecisionFactoryInterface $factory) use ($name): DecisionInterface {
            return $factory->createAffirmativeDecision($name);
        };
    }

    private function getCreateConsensusDecision(?string $name = null): \Closure
    {
        return static function (DecisionFactoryInterface $factory) use ($name): DecisionInterface {
            return $factory->createConsensusDecision($name);
        };
    }

    private function getCreateUnanimousDecision(?string $name = null): \Closure
    {
        return static function (DecisionFactoryInterface $factory) use ($name): DecisionInterface {
            return $factory->createUnanimousDecision($name);
        };
    }

    private function getCreateValueDecision(?string $name = null): \Closure
    {
        return static function (DecisionFactoryInterface $factory) use ($name): DecisionInterface {
            return $factory->createValueDecision($name);
        };
    }
}
