<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Exception;

use EonX\EasyDecision\Exception\EasyDecisionExceptionInterface as ExceptionInterface;
use InvalidArgumentException;

final class ArrayOutputRequiresOutputKeyException extends InvalidArgumentException implements ExceptionInterface
{
    // No body needed
}
