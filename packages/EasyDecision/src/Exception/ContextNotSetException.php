<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Exception;

use RuntimeException;

final class ContextNotSetException extends RuntimeException implements EasyDecisionExceptionInterface
{
    // No body needed
}
