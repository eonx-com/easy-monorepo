<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Exceptions;

use LoyaltyCorp\EasyDecision\Interfaces\EasyDecisionExceptionInterface;

final class InvalidDecisionException extends \InvalidArgumentException implements EasyDecisionExceptionInterface
{
    // No body needed.
}

\class_alias(
    InvalidDecisionException::class,
    'StepTheFkUp\EasyDecision\Exceptions\InvalidDecisionException',
    false
);
