<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Exceptions;

use StepTheFkUp\EasyDecision\Interfaces\EasyDecisionExceptionInterface;

final class MissingValueIndexException extends \InvalidArgumentException implements EasyDecisionExceptionInterface
{
    // No body needed.
}

\class_alias(
    MissingValueIndexException::class,
    'LoyaltyCorp\EasyDecision\Exceptions\MissingValueIndexException',
    false
);
