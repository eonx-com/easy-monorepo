<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Expressions;

use EonX\EasyDecision\Exceptions\InvalidExpressionException;
use EonX\EasyDecision\Expressions\ExpressionLanguageConfig;
use EonX\EasyDecision\Helpers\FromPhpExpressionFunctionProvider;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface;
use EonX\EasyDecision\Tests\AbstractTestCase;

final class ExpressionLanguageTest extends AbstractTestCase
{
    /**
     * @var string
     */
    private static $expression = '(max(1,2,3,4,5,6) + min(6,5,4,3,2,1) + 3) / (2 - input)';

    public function testGetFunctions(): void
    {
        $functions = $this->getExpressionLanguage()->getFunctions();

        self::assertCount(2, $functions);
        self::assertEquals('min', $functions[0]->getName());
        self::assertEquals('max', $functions[1]->getName());
    }

    public function testValidateInvalidExpression(): void
    {
        $this->expectException(InvalidExpressionException::class);

        $this->getExpressionLanguage()->validate(static::$expression, ['invalid']);
    }

    public function testValidateValidExpression(): void
    {
        self::assertTrue($this->getExpressionLanguage()->validate(static::$expression, ['input']));
    }

    private function getExpressionLanguage(): ExpressionLanguageInterface
    {
        return $this->getExpressionLanguageFactory()->create(new ExpressionLanguageConfig(null, [
            new FromPhpExpressionFunctionProvider(['min', 'max']),
        ]));
    }
}
