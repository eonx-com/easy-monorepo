<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

final class ConsensusDecision extends AbstractDecision
{
    /**
     * @var int
     */
    private $countFalse;

    /**
     * @var int
     */
    private $countTrue;

    /**
     * @param mixed $output
     */
    protected function doHandleRuleOutput($output): void
    {
        (bool)$output ? $this->countTrue++ : $this->countFalse++;
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
