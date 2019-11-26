<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Exceptions;

use LoyaltyCorp\EasyDecision\Interfaces\EasyDecisionExceptionInterface;

final class UnableToMakeDecisionException extends \RuntimeException implements EasyDecisionExceptionInterface
{
    // No body needed.
}
