<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\src\Aws\Helper;

use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;

final class LambdaContextHelperTest extends AbstractUnitTestCase
{
    public function testGetInvocationContextSucceeds(): void
    {
        LambdaContextHelper::getInvocationContext();

        // @phpstan-ignore-next-line Make fake assert to mark test as used assertion
        self::assertTrue(true);
    }

    public function testGetRequestContextSucceeds(): void
    {
        LambdaContextHelper::getRequestContext();

        // @phpstan-ignore-next-line Make fake assert to mark test as used assertion
        self::assertTrue(true);
    }
}
