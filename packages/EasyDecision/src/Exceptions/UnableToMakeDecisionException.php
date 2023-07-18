<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Exceptions;

use EonX\EasyDecision\Interfaces\EasyDecisionExceptionInterface;
use RuntimeException;

final class UnableToMakeDecisionException extends RuntimeException implements EasyDecisionExceptionInterface
{
    // No body needed
}
