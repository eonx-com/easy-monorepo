<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests;

use EonX\EasyDecision\Expressions\ExpressionLanguageFactory;
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
    private static ?ExpressionLanguageRuleFactoryInterface $languageRuleFactory = null;

    private ?ExpressionLanguageFactoryInterface $expressionLanguageFactory = null;

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }

        parent::tearDown();
    }

    /**
     * @param mixed[]|null $extra
     */
    protected static function createLanguageRule(
        string $expression,
        ?int $priority = null,
        ?string $name = null,
        ?array $extra = null,
    ): RuleInterface {
        return self::getLanguageRuleFactory()
            ->create($expression, $priority, $name, $extra);
    }

    protected function createExpressionLanguage(): ExpressionLanguageInterface
    {
        return $this->getExpressionLanguageFactory()
            ->create();
    }

    protected function createFalseRule(string $name, ?int $priority = null): RuleInterface
    {
        return new RuleStub($name, false, null, $priority);
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

        return $this->expressionLanguageFactory = new ExpressionLanguageFactory();
    }

    protected function injectExpressionLanguage(DecisionInterface $decision): void
    {
        $decision->setExpressionLanguage($this->createExpressionLanguage());
    }

    private static function getLanguageRuleFactory(): ExpressionLanguageRuleFactoryInterface
    {
        if (self::$languageRuleFactory !== null) {
            return self::$languageRuleFactory;
        }

        return self::$languageRuleFactory = new ExpressionLanguageRuleFactory();
    }
}
