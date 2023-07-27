<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Exceptions;

use EonX\EasyDecision\Interfaces\EasyDecisionExceptionInterface;

final class InvalidArgumentException extends \InvalidArgumentException implements EasyDecisionExceptionInterface
{
    // No body needed
}
