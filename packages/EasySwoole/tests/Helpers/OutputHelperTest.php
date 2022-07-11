<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Helpers;

use EonX\EasySwoole\Helpers\OutputHelper;
use EonX\EasySwoole\Tests\AbstractTestCase;

final class OutputHelperTest extends AbstractTestCase
{
    public function testWriteln(): void
    {
        $message = 'hey there!!!';
        OutputHelper::writeln($message);

        self::assertTrue(true);
    }
}
