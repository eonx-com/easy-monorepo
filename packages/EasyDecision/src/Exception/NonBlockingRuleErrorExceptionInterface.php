<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Exception;

interface NonBlockingRuleErrorExceptionInterface
{
    public function getErrorOutput(): string;
}
