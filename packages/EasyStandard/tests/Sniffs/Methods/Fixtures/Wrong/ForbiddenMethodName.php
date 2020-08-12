<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Methods\Fixtures\Wrong;

final class ForbiddenMethodName
{
    public function testCreateSucceedWithSomething(): void
    {
        // No body needed here
    }
}
