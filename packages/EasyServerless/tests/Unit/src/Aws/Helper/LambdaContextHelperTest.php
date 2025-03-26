<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\src\Aws\Helper;

use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
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
