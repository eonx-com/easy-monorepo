<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

final class ConsensusDecision extends AbstractDecision
{
    private int $countFalse = 0;

    private int $countTrue = 0;

    protected function doHandleRuleOutput(mixed $output): void
    {
        $output ? $this->countTrue++ : $this->countFalse++;
    }

    protected function doMake(): bool
    {
        return $this->countTrue >= $this->countFalse;
    }

    protected function getDefaultOutput(): bool
    {
        return true;
    }

    protected function reset(): void
    {
        $this->countFalse = 0;
        $this->countTrue = 0;
    }
}
