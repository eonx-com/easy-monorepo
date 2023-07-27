<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface NonBlockingRuleErrorInterface
{
    public function getErrorOutput(): string;
}
