<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Tests\Expressions;

use StepTheFkUp\EasyDecision\Exceptions\InvalidExpressionException;
use StepTheFkUp\EasyDecision\Expressions\ExpressionLanguageConfig;
use StepTheFkUp\EasyDecision\Helpers\FromPhpExpressionFunctionProvider;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface;
use StepTheFkUp\EasyDecision\Tests\AbstractTestCase;

final class ExpressionLanguageTest extends AbstractTestCase
{
    /**
     * @var string
     */
    private static $expression = '(max(1,2,3,4,5,6) + min(6,5,4,3,2,1) + 3) / (2 - input)';

    /**
     * ExpressionLanguage should throw an exception if given expression is invalid for given names.
     *
     * @return void
     */
    public function testValidateInvalidExpression(): void
    {
        $this->expectException(InvalidExpressionException::class);

        $this->getExpressionLanguage()->validate(static::$expression, ['invalid']);
    }

    /**
     * ExpressionLanguage should return true if given expression is valid for given names.
     *
     * @return void
     */
    public function testValidateValidExpression(): void
    {
        self::assertTrue($this->getExpressionLanguage()->validate(static::$expression, ['input']));
    }

    /**
     * Get expression language.
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface
     */
    private function getExpressionLanguage(): ExpressionLanguageInterface
    {
        return $this->getExpressionLanguageFactory()->create(new ExpressionLanguageConfig(null, [
            new FromPhpExpressionFunctionProvider(['min', 'max'])
        ]));
    }
}
