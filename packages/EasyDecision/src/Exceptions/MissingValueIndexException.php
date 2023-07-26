<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Exceptions;

use EonX\EasyDecision\Interfaces\EasyDecisionExceptionInterface;
use InvalidArgumentException;

final class MissingValueIndexException extends InvalidArgumentException implements EasyDecisionExceptionInterface
{
    // No body needed
}
