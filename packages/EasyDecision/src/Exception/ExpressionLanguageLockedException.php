<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Exception;

use RuntimeException;

final class ExpressionLanguageLockedException extends RuntimeException implements ExpressionLanguageExceptionInterface
{
    // No body needed
}
