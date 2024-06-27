<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Unit\Factory;

use EonX\EasyDecision\ExpressionFunction\ExpressionFunction;
use EonX\EasyDecision\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\ExpressionLanguage\ExpressionFunction as BaseExpressionFunction;

final class ExpressionLanguageFactoryTest extends AbstractUnitTestCase
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
