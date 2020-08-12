<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Methods\Fixtures\Wrong;

final class NotPermittedMethodName
{
    public function testmethod(): void
    {
        // No body needed here
    }
}
