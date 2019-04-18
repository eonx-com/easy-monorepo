<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Exceptions;

use StepTheFkUp\EasyDecision\Interfaces\EasyDecisionExceptionInterface;

final class ContextNotSetException extends \RuntimeException implements EasyDecisionExceptionInterface
{
    // No body needed.
}

\class_alias(
    ContextNotSetException::class,
    'LoyaltyCorp\EasyDecision\Exceptions\ContextNotSetException',
    false
);
