<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Exceptions;

use LoyaltyCorp\EasyDecision\Interfaces\EasyDecisionExceptionInterface;

final class ReservedContextIndexException extends \InvalidArgumentException implements EasyDecisionExceptionInterface
{
    // No body needed.
}

\class_alias(
    ReservedContextIndexException::class,
    'StepTheFkUp\EasyDecision\Exceptions\ReservedContextIndexException',
    false
);
