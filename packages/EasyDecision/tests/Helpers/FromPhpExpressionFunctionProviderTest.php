<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Helpers;

use EonX\EasyDecision\Helpers\FromPhpExpressionFunctionProvider;
use EonX\EasyDecision\Tests\AbstractTestCase;

/**
 * @coversNothing
 */
final class FromPhpExpressionFunctionProviderTest extends AbstractTestCase
{
    public function testGetFunctions(): void
    {
        $phpFunctions = ['max', 'min', 'spl_object_hash', 'is_array'];
        $expressionFunctions = (new FromPhpExpressionFunctionProvider($phpFunctions))->getFunctions();

        foreach ($phpFunctions as $key => $phpFunction) {
            self::assertEquals($phpFunction, $expressionFunctions[$key]->getName());
        }
    }
}
