<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Exceptions;

use LoyaltyCorp\EasyDecision\Interfaces\EasyDecisionExceptionInterface;

final class EmptyRulesException extends \InvalidArgumentException implements EasyDecisionExceptionInterface
{
    // No body needed.
}

\class_alias(
    EmptyRulesException::class,
    'StepTheFkUp\EasyDecision\Exceptions\EmptyRulesException',
    false
);
