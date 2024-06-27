<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Exception;

use EonX\EasyDecision\Exception\EasyDecisionExceptionInterface as ExceptionInterface;
use RuntimeException;

final class ExpressionLanguageNotSetOnDecisionException extends RuntimeException implements ExceptionInterface
{
    // No body needed
}
