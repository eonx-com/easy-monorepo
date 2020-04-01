<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests;

use EonX\EasyDecision\Expressions\ExpressionFunctionFactory;
use EonX\EasyDecision\Expressions\ExpressionLanguageConfig;
use EonX\EasyDecision\Expressions\ExpressionLanguageFactory;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageConfigInterface;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageFactoryInterface;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageInterface;
use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Interfaces\ExpressionLanguageRuleFactoryInterface;
use EonX\EasyDecision\Interfaces\RuleInterface;
use EonX\EasyDecision\Rules\ExpressionLanguageRuleFactory;
use EonX\EasyDecision\Tests\Stubs\RuleStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @var \EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageFactoryInterface
     */
    private $expressionLanguageFactory;

    /**
     * @var \EonX\EasyDecision\Interfaces\ExpressionLanguageRuleFactoryInterface
     */
    private $languageRuleFactory;

    protected function createExpressionLanguage(
        ?ExpressionLanguageConfigInterface $config = null
    ): ExpressionLanguageInterface {
        return $this->getExpressionLanguageFactory()->create($config ?? new ExpressionLanguageConfig());
    }

    protected function createFalseRule(string $name, ?int $priority = null): RuleInterface
    {
        return new RuleStub($name, false, null, $priority);
    }

    /**
     * @param null|mixed[] $extra
     */
    protected function createLanguageRule(
        string $expression,
        ?int $priority = null,
        ?string $name = null,
        ?array $extra = null
    ): RuleInterface {
        return $this->getLanguageRuleFactory()->create($expression, $priority, $name, $extra);
    }

    protected function createTrueRule(string $name, ?int $priority = null): RuleInterface
    {
        return new RuleStub($name, true, null, $priority);
    }

    protected function createUnsupportedRule(string $name, ?int $priority = null): RuleInterface
    {
        return new RuleStub($name, null, false, $priority);
    }

    protected function getExpressionLanguageFactory(): ExpressionLanguageFactoryInterface
    {
        if ($this->expressionLanguageFactory !== null) {
            return $this->expressionLanguageFactory;
        }

        return $this->expressionLanguageFactory = new ExpressionLanguageFactory(new ExpressionFunctionFactory());
    }

    protected function injectExpressionLanguage(
        DecisionInterface $decision,
        ?ExpressionLanguageConfigInterface $config = null
    ): void {
        $decision->setExpressionLanguage($this->createExpressionLanguage($config));
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }

        parent::tearDown();
    }

    private function getLanguageRuleFactory(): ExpressionLanguageRuleFactoryInterface
    {
        if ($this->languageRuleFactory !== null) {
            return $this->languageRuleFactory;
        }

        return $this->languageRuleFactory = new ExpressionLanguageRuleFactory();
    }
}
