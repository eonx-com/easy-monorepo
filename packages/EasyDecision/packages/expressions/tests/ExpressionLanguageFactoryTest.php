<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Expressions\Tests;

use EonX\EasyDecision\Expressions\ExpressionFunction;
use EonX\EasyDecision\Tests\AbstractTestCase;
use Symfony\Component\ExpressionLanguage\ExpressionFunction as BaseExpressionFunction;

final class ExpressionLanguageFactoryTest extends AbstractTestCase
{
    public function testCreateWithFunctions(): void
    {
        $expressionLanguage = $this
            ->createExpressionLanguage()
            ->addFunctions([
                new ExpressionFunction('min', BaseExpressionFunction::fromPhp('min')->getEvaluator()),
                new ExpressionFunction('max', BaseExpressionFunction::fromPhp('max')->getEvaluator()),
            ]);

        self::assertEquals(4, $expressionLanguage->evaluate('max(1,2) + min(2,3)'));
    }
}
