<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Helpers;

use EonX\EasySwoole\Helpers\FunctionHelper;
use EonX\EasySwoole\Tests\AbstractTestCase;

final class FunctionHelperTest extends AbstractTestCase
{
    public function testCountCpu(): void
    {
        self::assertIsInt(FunctionHelper::countCpu());
    }
}
