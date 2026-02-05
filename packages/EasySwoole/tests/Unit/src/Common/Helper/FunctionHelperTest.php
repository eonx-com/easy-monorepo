<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Unit\Common\Helper;

use EonX\EasySwoole\Common\Helper\FunctionHelper;
use EonX\EasySwoole\Tests\Unit\AbstractUnitTestCase;

final class FunctionHelperTest extends AbstractUnitTestCase
{
    public function testCountCpu(): void
    {
        FunctionHelper::countCpu();

        // @phpstan-ignore-next-line Make fake assert to mark test as used assertion
        self::assertTrue(true);
    }
}
