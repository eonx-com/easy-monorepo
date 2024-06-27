<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Unit\Logging\Helper;

use EonX\EasySwoole\Logging\Helper\OutputHelper;
use EonX\EasySwoole\Tests\Unit\AbstractUnitTestCase;

final class OutputHelperTest extends AbstractUnitTestCase
{
    public function testWriteln(): void
    {
        $message = 'hey there!!!';
        OutputHelper::writeln($message);

        self::assertTrue(true);
    }
}
