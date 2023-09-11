<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Expressions\Tests;

use EonX\EasyDecision\Exceptions\InvalidExpressionException;
use EonX\EasyDecision\Expressions\Exceptions\ExpressionLanguageLockedException;
use EonX\EasyDecision\Expressions\ExpressionFunction;
use EonX\EasyDecision\Expressions\ExpressionLanguage;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageInterface;
use EonX\EasyDecision\Tests\AbstractTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\ExpressionLanguage\ExpressionFunction as BaseExpressionFunction;

final class ExpressionLanguageTest extends AbstractTestCase
{
    private static string $expression = '(max(1,2,3,4,5,6) + min(6,5,4,3,2,1) + 3) / (2 - input)';

    public function testAddRemoveFunctions(): void
    {
        $function = new ExpressionFunction('my-function', function (): void {
        }, 'my-description');

        $expressionLanguage = $this->getExpressionLanguage()
            ->setCache(new ArrayAdapter());

        $expressionLanguage->addFunction($function);
        $expressionLanguage->removeFunction('min');
        $expressionLanguage->removeFunctions(['do-not-exist']);

        $functions = $expressionLanguage->getFunctions();

        self::assertCount(2, $functions);
        self::assertEquals('max', $functions[0]->getName());
        self::assertEquals('my-function', $functions[1]->getName());
        self::assertEquals('my-description', $functions[1]->getDescription());
    }

    public function testExpressionLanguageLocked(): void
    {
        $this->expectException(ExpressionLanguageLockedException::class);

        $expressionLanguage = $this->getExpressionLanguage();
        $expressionLanguage->evaluate('max(1, 2)');

        $expressionLanguage->setCache(new ArrayAdapter());
    }

    public function testGetFunctions(): void
    {
        $functions = $this->getExpressionLanguage()
            ->getFunctions();

        self::assertCount(2, $functions);
        self::assertEquals('min', $functions[0]->getName());
        self::assertEquals('max', $functions[1]->getName());
    }

    public function testValidateInvalidExpression(): void
    {
        $this->expectException(InvalidExpressionException::class);

        $this->getExpressionLanguage()
            ->validate(self::$expression, ['invalid']);
    }

    public function testValidateValidExpression(): void
    {
        self::assertTrue($this->getExpressionLanguage()->validate(self::$expression, ['input']));
    }

    private function getExpressionLanguage(): ExpressionLanguageInterface
    {
        $expressionLanguage = new ExpressionLanguage();
        $expressionLanguage->addFunctions([
            new ExpressionFunction('min', BaseExpressionFunction::fromPhp('min')->getEvaluator()),
            new ExpressionFunction('max', BaseExpressionFunction::fromPhp('max')->getEvaluator()),
        ]);

        return $expressionLanguage;
    }
}
