<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stubs;

use EonX\EasyDecision\Decisions\AbstractDecision;

final class DecisionStub extends AbstractDecision
{
    protected function doHandleRuleOutput(mixed $output): void
    {
        // No body needed
    }

    protected function doMake(): bool
    {
        return true;
    }

    protected function getDefaultOutput(): bool
    {
        return true;
    }

    protected function reset(): void
    {
        // No body needed
    }
}
