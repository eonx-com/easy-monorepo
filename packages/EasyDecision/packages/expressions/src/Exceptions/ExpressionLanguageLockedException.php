<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Expressions\Exceptions;

use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageExceptionInterface;
use RuntimeException;

final class ExpressionLanguageLockedException extends RuntimeException implements ExpressionLanguageExceptionInterface
{
    // No body needed
}
