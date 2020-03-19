<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

final class ConsensusDecision extends AbstractDecision
{
    /**
     * @var int
     */
    private $countFalse = 0;

    /**
     * @var int
     */
    private $countTrue = 0;

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
}
