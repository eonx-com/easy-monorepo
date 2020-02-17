<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Exceptions;

use EonX\EasyDecision\Interfaces\EasyDecisionExceptionInterface as ExceptionInterface;

final class ArrayOutputRequiresOutputKeyException extends \InvalidArgumentException implements ExceptionInterface
{
    // No body needed.
}
