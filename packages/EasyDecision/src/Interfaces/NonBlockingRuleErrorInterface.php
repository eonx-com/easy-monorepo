<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces;

interface NonBlockingRuleErrorInterface
{
    /**
     * Get error output.
     *
     * @return string
     */
    public function getErrorOutput(): string;
}
