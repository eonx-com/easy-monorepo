<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Exceptions;

use StepTheFkUp\EasyDecision\Interfaces\EasyDecisionExceptionInterface;

final class EmptyRulesException extends \InvalidArgumentException implements EasyDecisionExceptionInterface
{
    // No body needed.
}

\class_alias(
    EmptyRulesException::class,
    'LoyaltyCorp\EasyDecision\Exceptions\EmptyRulesException',
    false
);
