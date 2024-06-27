<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stub\Decision;

use EonX\EasyDecision\Decision\AbstractDecision;

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
