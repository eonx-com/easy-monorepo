<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Unit;

use EonX\EasyDecision\Decision\DecisionInterface;
use EonX\EasyDecision\ExpressionLanguage\ExpressionLanguageInterface;
use EonX\EasyDecision\Factory\ExpressionLanguageFactory;
use EonX\EasyDecision\Factory\ExpressionLanguageFactoryInterface;
use EonX\EasyDecision\Factory\ExpressionLanguageRuleFactory;
use EonX\EasyDecision\Factory\ExpressionLanguageRuleFactoryInterface;
use EonX\EasyDecision\Rule\RuleInterface;
use EonX\EasyDecision\Tests\Stub\Rule\RuleStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractUnitTestCase extends TestCase
{
    private static ?ExpressionLanguageRuleFactoryInterface $languageRuleFactory = null;

    private ?ExpressionLanguageFactoryInterface $expressionLanguageFactory = null;

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $filesystem = new Filesystem();
        $var = __DIR__ . '/../../var';

        if ($filesystem->exists($var)) {
            $filesystem->remove($var);
        }
    }

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
