<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Exceptions;

use EonX\EasyDecision\Interfaces\EasyDecisionExceptionInterface;

final class ContextNotSetException extends \RuntimeException implements EasyDecisionExceptionInterface
{
    // No body needed.
}
