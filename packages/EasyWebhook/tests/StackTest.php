<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests;

use EonX\EasyWebhook\Exceptions\InvalidStackIndexException;
use EonX\EasyWebhook\Stack;

final class StackTest extends AbstractTestCase
{
    public function testRewindToThrowsExceptionIfIndexNotPositive(): void
    {
        $this->expectException(InvalidStackIndexException::class);
        $this->expectExceptionMessage('Stack index must be positive, -1 given');

        (new Stack([]))->rewindTo(-1);
    }
}
