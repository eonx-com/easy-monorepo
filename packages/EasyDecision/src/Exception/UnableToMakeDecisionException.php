<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Exception;

use RuntimeException;

final class UnableToMakeDecisionException extends RuntimeException implements EasyDecisionExceptionInterface
{
    // No body needed
}
