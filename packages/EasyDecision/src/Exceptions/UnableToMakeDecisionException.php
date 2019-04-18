<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Exceptions;

use StepTheFkUp\EasyDecision\Interfaces\EasyDecisionExceptionInterface;

final class UnableToMakeDecisionException extends \RuntimeException implements EasyDecisionExceptionInterface
{
    // No body needed.
}

\class_alias(
    UnableToMakeDecisionException::class,
    'LoyaltyCorp\EasyDecision\Exceptions\UnableToMakeDecisionException',
    false
);
