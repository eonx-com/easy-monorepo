<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Stack;

use EonX\EasyWebhook\Common\Exception\InvalidStackIndexException;
use EonX\EasyWebhook\Common\Stack\Stack;
use EonX\EasyWebhook\Tests\Unit\AbstractUnitTestCase;

final class StackTest extends AbstractUnitTestCase
{
    public function testRewindToThrowsExceptionIfIndexNotPositive(): void
    {
        $this->expectException(InvalidStackIndexException::class);
        $this->expectExceptionMessage('Stack index must be positive, -1 given');

        (new Stack([]))->rewindTo(-1);
    }
}
