<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests;

use EonX\EasyDecision\Expressions\ExpressionFunctionFactory;
use EonX\EasyDecision\Expressions\ExpressionLanguageConfig;
use EonX\EasyDecision\Expressions\ExpressionLanguageFactory;
use EonX\EasyDecision\Interfaces\ExpressionLanguageRuleFactoryInterface;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface;
use EonX\EasyDecision\Interfaces\RuleInterface;
use EonX\EasyDecision\Rules\ExpressionLanguageRuleFactory;
use EonX\EasyDecision\Tests\Stubs\RuleStub;
use PHPUnit\Framework\TestCase;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @var \EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface
     */
    private $expressionLanguageFactory;

    /**
     * @var \EonX\EasyDecision\Interfaces\ExpressionLanguageRuleFactoryInterface
     */
    private $languageRuleFactory;

    /**
     * Create expression language for given config.
     *
     * @param null|\EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface $config
     *
     * @return \EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface
     */
    protected function createExpressionLanguage(
        ?ExpressionLanguageConfigInterface $config = null
    ): ExpressionLanguageInterface {
        return $this->getExpressionLanguageFactory()->create($config ?? new ExpressionLanguageConfig());
    }

    /**
     * Create rule which returns false.
     *
     * @param string $name
     * @param null|int $priority
     *
     * @return \EonX\EasyDecision\Interfaces\RuleInterface
     */
    protected function createFalseRule(string $name, ?int $priority = null): RuleInterface
    {
        return new RuleStub($name, false, null, $priority);
    }

    /**
     * Create expression language rule.
     *
     * @param string $expression
     * @param null|int $priority
     *
     * @return \EonX\EasyDecision\Interfaces\RuleInterface
     */
    protected function createLanguageRule(string $expression, ?int $priority = null): RuleInterface
    {
        return $this->getLanguageRuleFactory()->create($expression, $priority);
    }

    /**
     * Create rule which returns true.
     *
     * @param string $name
     * @param null|int $priority
     *
     * @return \EonX\EasyDecision\Interfaces\RuleInterface
     */
    protected function createTrueRule(string $name, ?int $priority = null): RuleInterface
    {
        return new RuleStub($name, true, null, $priority);
    }

    /**
     * Create rule which will be unsupported.
     *
     * @param string $name
     * @param null|int $priority
     *
     * @return \EonX\EasyDecision\Interfaces\RuleInterface
     */
    protected function createUnsupportedRule(string $name, ?int $priority = null): RuleInterface
    {
        return new RuleStub($name, null, false, $priority);
    }

    /**
     * Get expression language factory.
     *
     * @return \EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface
     */
    protected function getExpressionLanguageFactory(): ExpressionLanguageFactoryInterface
    {
        if ($this->expressionLanguageFactory !== null) {
            return $this->expressionLanguageFactory;
        }

        return $this->expressionLanguageFactory = new ExpressionLanguageFactory(new ExpressionFunctionFactory());
    }

    /**
     * Get expression language rule factory.
     *
     * @return \EonX\EasyDecision\Interfaces\ExpressionLanguageRuleFactoryInterface
     */
    private function getLanguageRuleFactory(): ExpressionLanguageRuleFactoryInterface
    {
        if ($this->languageRuleFactory !== null) {
            return $this->languageRuleFactory;
        }

        return $this->languageRuleFactory = new ExpressionLanguageRuleFactory();
    }
}


