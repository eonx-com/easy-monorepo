<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Exceptions;

use EonX\EasyDecision\Interfaces\EasyDecisionExceptionInterface;

final class ReservedContextIndexException extends \InvalidArgumentException implements EasyDecisionExceptionInterface
{
    // No body needed.
}
