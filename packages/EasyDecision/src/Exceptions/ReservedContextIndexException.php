<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Exceptions;

use StepTheFkUp\EasyDecision\Interfaces\EasyDecisionExceptionInterface;

final class ReservedContextIndexException extends \InvalidArgumentException implements EasyDecisionExceptionInterface
{
    // No body needed.
}

\class_alias(
    ReservedContextIndexException::class,
    'LoyaltyCorp\EasyDecision\Exceptions\ReservedContextIndexException',
    false
);
