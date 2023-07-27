<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Exceptions;

use EonX\EasyDecision\Interfaces\EasyDecisionExceptionInterface as ExceptionInterface;
use RuntimeException;

final class ExpressionLanguageNotSetOnDecisionException extends RuntimeException implements ExceptionInterface
{
    // No body needed
}
