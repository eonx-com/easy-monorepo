<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Tests;

use PHPUnit\Framework\TestCase;
use StepTheFkUp\EasyDecision\Expressions\ExpressionFunctionFactory;
use StepTheFkUp\EasyDecision\Expressions\ExpressionLanguageConfig;
use StepTheFkUp\EasyDecision\Expressions\ExpressionLanguageFactory;
use StepTheFkUp\EasyDecision\Interfaces\ExpressionLanguageRuleFactoryInterface;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface;
use StepTheFkUp\EasyDecision\Interfaces\RuleInterface;
use StepTheFkUp\EasyDecision\Rules\ExpressionLanguageRuleFactory;
use StepTheFkUp\EasyDecision\Tests\Stubs\RuleStub;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @var \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface
     */
    private $expressionLanguageFactory;

    /**
     * @var \StepTheFkUp\EasyDecision\Interfaces\ExpressionLanguageRuleFactoryInterface
     */
    private $languageRuleFactory;

    /**
     * Create expression language for given config.
     *
     * @param null|\StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface $config
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface
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
     * @return \StepTheFkUp\EasyDecision\Interfaces\RuleInterface
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
     * @return \StepTheFkUp\EasyDecision\Interfaces\RuleInterface
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
     * @return \StepTheFkUp\EasyDecision\Interfaces\RuleInterface
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
     * @return \StepTheFkUp\EasyDecision\Interfaces\RuleInterface
     */
    protected function createUnsupportedRule(string $name, ?int $priority = null): RuleInterface
    {
        return new RuleStub($name, null, false, $priority);
    }

    /**
     * Get expression language factory.
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface
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
     * @return \StepTheFkUp\EasyDecision\Interfaces\ExpressionLanguageRuleFactoryInterface
     */
    private function getLanguageRuleFactory(): ExpressionLanguageRuleFactoryInterface
    {
        if ($this->languageRuleFactory !== null) {
            return $this->languageRuleFactory;
        }

        return $this->languageRuleFactory = new ExpressionLanguageRuleFactory();
    }
}
