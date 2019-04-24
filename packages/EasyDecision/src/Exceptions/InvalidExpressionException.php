<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Exceptions;

use LoyaltyCorp\EasyDecision\Interfaces\EasyDecisionExceptionInterface;

final class InvalidExpressionException extends \InvalidArgumentException implements EasyDecisionExceptionInterface
{
    // No body needed.
}

\class_alias(
    InvalidExpressionException::class,
    'StepTheFkUp\EasyDecision\Exceptions\InvalidExpressionException',
    false
);
