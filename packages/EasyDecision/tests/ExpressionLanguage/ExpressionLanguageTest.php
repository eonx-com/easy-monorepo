<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Tests\ExpressionLanguage;

use StepTheFkUp\EasyDecision\Tests\AbstractTestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ExpressionLanguageTest extends AbstractTestCase
{
    public function testWhatever(): void
    {
        $expr = new ExpressionLanguage();

        $expr->register('add', function ($value) {}, function (array $arguments, $value) {
            return $arguments['output'] + $value;
        });

        $values = [
            'value' => 10,
            'output' => 100
        ];

        \var_dump($expr->compile('add(value)', \array_keys($values)));
        \var_dump($expr->evaluate('add(value)', $values));

        self::assertTrue(true);
    }
}
