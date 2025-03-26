<?php
declare(strict_types=1);

namespace Unit\src\Aws;

use EonX\EasyServerless\Aws\LambdaContextHelper;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;

final class LambdaContextHelperTest extends AbstractUnitTestCase
{
    public function testGetInvocationContextSucceeds(): void
    {
        self::assertIsArray(LambdaContextHelper::getInvocationContext());
    }

    public function testGetRequestContextSucceeds(): void
    {
        self::assertIsArray(LambdaContextHelper::getRequestContext());
    }
}
