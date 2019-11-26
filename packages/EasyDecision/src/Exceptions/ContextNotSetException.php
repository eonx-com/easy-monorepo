<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Exceptions;

use LoyaltyCorp\EasyDecision\Interfaces\EasyDecisionExceptionInterface;

final class ContextNotSetException extends \RuntimeException implements EasyDecisionExceptionInterface
{
    // No body needed.
}
