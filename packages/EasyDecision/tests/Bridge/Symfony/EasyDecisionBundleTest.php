<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Bridge\Symfony;

use EonX\EasyDecision\Decisions\AffirmativeDecision;
use EonX\EasyDecision\Decisions\ConsensusDecision;
use EonX\EasyDecision\Decisions\UnanimousDecision;
use EonX\EasyDecision\Decisions\ValueDecision;
use EonX\EasyDecision\Exceptions\InvalidMappingException;
use EonX\EasyDecision\Exceptions\UnableToMakeDecisionException;
use EonX\EasyDecision\Interfaces\DecisionFactoryInterface;
use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Tests\AbstractTestCase;
use EonX\EasyDecision\Tests\Bridge\Symfony\Stubs\KernelStub;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

final class EasyDecisionBundleTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testDecisions
     */
    public static function providerCreateDecision(): iterable
    {
        yield 'Affirmative decision with no configurators' => [
            self::getCreateAffirmativeDecision(),
            static function (DecisionInterface $decision): void {
                self::assertInstanceOf(AffirmativeDecision::class, $decision);
                self::assertSame('<no-name>', $decision->getName());
            },
        ];

        yield 'Consensus decision with no configurators' => [
            self::getCreateConsensusDecision(),
            static function (DecisionInterface $decision): void {
                self::assertInstanceOf(ConsensusDecision::class, $decision);
                self::assertSame('<no-name>', $decision->getName());
            },
        ];

        yield 'Unanimous decision with no configurators' => [
            self::getCreateUnanimousDecision(),
            static function (DecisionInterface $decision): void {
                self::assertInstanceOf(UnanimousDecision::class, $decision);
                self::assertSame('<no-name>', $decision->getName());
            },
        ];

        yield 'Value decision with no configurators' => [
            self::getCreateValueDecision(),
            static function (DecisionInterface $decision): void {
                self::assertInstanceOf(ValueDecision::class, $decision);
                self::assertSame('<no-name>', $decision->getName());
                self::assertSame(1, $decision->make([
                    'value' => 1,
                ]));
            },
        ];

        yield 'Value decision with name configurator' => [
            self::getCreateValueDecision(),
            static function (DecisionInterface $decision): void {
                self::assertInstanceOf(ValueDecision::class, $decision);
                self::assertSame('my-value-decision', $decision->getName());
                self::assertSame(1, $decision->make([
                    'value' => 1,
                ]));
            },
            [__DIR__ . '/Fixtures/value_with_name.php'],
        ];

        yield 'Value decision with rules configurator' => [
            self::getCreateValueDecision(),
            static function (DecisionInterface $decision): void {
                self::assertInstanceOf(ValueDecision::class, $decision);
                self::assertSame(11, $decision->make([
                    'value' => 1,
                ]));
            },
            [__DIR__ . '/Fixtures/value_with_rules_and_expression_language.php'],
        ];

        yield 'Value decision with name restricted configurator supported' => [
            self::getCreateValueDecision('restricted'),
            static function (DecisionInterface $decision): void {
                self::assertInstanceOf(ValueDecision::class, $decision);

                $functions = [];
                $expressionLanguage = $decision->getExpressionLanguage();

                if ($expressionLanguage !== null) {
                    $functions = $expressionLanguage->getFunctions();
                }

                self::assertCount(1, $functions);
                self::assertSame('restricted', $functions[0]->getName());
            },
            [__DIR__ . '/Fixtures/value_with_name_restricted_expression_function.php'],
        ];

        yield 'Value decision with name restricted configurator not supported' => [
            self::getCreateValueDecision('not-restricted'),
            static function (DecisionInterface $decision): void {
                self::assertInstanceOf(ValueDecision::class, $decision);

                $functions = [];
                $expressionLanguage = $decision->getExpressionLanguage();

                if ($expressionLanguage !== null) {
                    $functions = $expressionLanguage->getFunctions();
                }

                self::assertEmpty($functions);
            },
            [__DIR__ . '/Fixtures/value_with_name_restricted_expression_function.php'],
        ];

        yield 'Decision with type restricted configurator supported' => [
            self::getCreateValueDecision(),
            static function (DecisionInterface $decision): void {
                self::assertInstanceOf(ValueDecision::class, $decision);

                $functions = [];
                $expressionLanguage = $decision->getExpressionLanguage();

                if ($expressionLanguage !== null) {
                    $functions = $expressionLanguage->getFunctions();
                }

                self::assertCount(1, $functions);
                self::assertSame('restricted', $functions[0]->getName());
            },
            [__DIR__ . '/Fixtures/value_with_type_restricted_expression_function.php'],
        ];

        yield 'Decision with type restricted configurator not supported' => [
            self::getCreateUnanimousDecision(),
            static function (DecisionInterface $decision): void {
                self::assertInstanceOf(UnanimousDecision::class, $decision);

                $functions = [];
                $expressionLanguage = $decision->getExpressionLanguage();

                if ($expressionLanguage !== null) {
                    $functions = $expressionLanguage->getFunctions();
                }

                self::assertEmpty($functions);
            },
            [__DIR__ . '/Fixtures/value_with_type_restricted_expression_function.php'],
        ];
    }

    public function testConfigurationWithNonexistentDecisionClassThrowsInvalidConfigurationException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'Invalid configuration for path "easy_decision.type_mapping.decision":' .
            ' Class "NonExistentDecisionClass" does not exist.'
        );

        $kernel = new KernelStub([
            __DIR__ . '/Fixtures/type_mapping_configuration_with_nonexistent_decision_class.php',
        ]);
        $kernel->boot();
    }

    public function testCreateByNameDecisionSucceeds(): void
    {
        $kernel = new KernelStub([__DIR__ . '/Fixtures/decision_by_name.php']);
        $kernel->boot();
        $factory = $kernel->getContainer()
            ->get(DecisionFactoryInterface::class);

        $decision = $factory->createByName('global_event_value_decision');

        self::assertInstanceOf(ValueDecision::class, $decision);
    }

    public function testCreateByNameDecisionThrowsInvalidMappingException(): void
    {
        $this->expectException(InvalidMappingException::class);
        $this->expectExceptionMessage('Decision for name "non-configured-decision" is not configured');

        $kernel = new KernelStub([__DIR__ . '/Fixtures/decision_by_name.php']);
        $kernel->boot();
        $factory = $kernel->getContainer()
            ->get(DecisionFactoryInterface::class);

        $factory->createByName('non-configured-decision');
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

        $factory = $kernel->getContainer()
            ->get(DecisionFactoryInterface::class);
        $decision = \call_user_func($create, $factory);

        \call_user_func($assert, $decision);
    }

    public function testNotExpressionLanguageException(): void
    {
        $this->expectException(UnableToMakeDecisionException::class);
        $this->expectExceptionMessage(
            'Decision "<no-name>" of type "EonX\EasyDecision\Decisions\ValueDecision": ' .
            'Expression language not set, to use it in your rules you must set it on the decision instance'
        );

        $kernel = new KernelStub([
            __DIR__ . '/Fixtures/disable_expression_language.php',
            __DIR__ . '/Fixtures/value_with_rules_and_expression_language.php',
        ]);

        $kernel->boot();

        $factory = $kernel->getContainer()
            ->get(DecisionFactoryInterface::class);
        $factory->createValueDecision()
            ->make([
                'value' => 1,
            ]);
    }

    private static function getCreateAffirmativeDecision(?string $name = null): \Closure
    {
        return static function (DecisionFactoryInterface $factory) use ($name): DecisionInterface {
            return $factory->createAffirmativeDecision($name);
        };
    }

    private static function getCreateConsensusDecision(?string $name = null): \Closure
    {
        return static function (DecisionFactoryInterface $factory) use ($name): DecisionInterface {
            return $factory->createConsensusDecision($name);
        };
    }

    private static function getCreateUnanimousDecision(?string $name = null): \Closure
    {
        return static function (DecisionFactoryInterface $factory) use ($name): DecisionInterface {
            return $factory->createUnanimousDecision($name);
        };
    }

    private static function getCreateValueDecision(?string $name = null): \Closure
    {
        return static function (DecisionFactoryInterface $factory) use ($name): DecisionInterface {
            return $factory->createValueDecision($name);
        };
    }
}
