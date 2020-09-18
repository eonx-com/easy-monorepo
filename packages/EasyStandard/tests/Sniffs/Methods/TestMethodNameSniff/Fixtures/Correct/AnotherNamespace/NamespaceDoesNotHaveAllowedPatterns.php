<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Methods\TestMethodNameSniff\Fixtures\Correct\AnotherNamespace;

final class NamespaceDoesNotHaveAllowedPatterns
{
    public function testmethod(): void
    {
        // No body needed here
    }
}
