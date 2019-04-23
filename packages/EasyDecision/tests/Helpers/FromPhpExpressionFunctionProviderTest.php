<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Tests\Helpers;

use LoyaltyCorp\EasyDecision\Helpers\FromPhpExpressionFunctionProvider;
use LoyaltyCorp\EasyDecision\Tests\AbstractTestCase;

final class FromPhpExpressionFunctionProviderTest extends AbstractTestCase
{
    /**
     * Provider should create expression functions from php functions names.
     *
     * @return void
     */
    public function testGetFunctions(): void
    {
        $phpFunctions = ['max', 'min', 'spl_object_hash', 'is_array'];
        $expressionFunctions = (new FromPhpExpressionFunctionProvider($phpFunctions))->getFunctions();

        foreach ($phpFunctions as $key => $phpFunction) {
            self::assertEquals($phpFunction, $expressionFunctions[$key]->getName());
        }
    }
}

\class_alias(
    FromPhpExpressionFunctionProviderTest::class,
    'StepTheFkUp\EasyDecision\Tests\Helpers\FromPhpExpressionFunctionProviderTest',
    false
);
