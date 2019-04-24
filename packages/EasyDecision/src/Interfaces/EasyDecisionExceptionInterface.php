<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces;

interface EasyDecisionExceptionInterface
{
    // Marker for all exceptions of this package.
}

\class_alias(
    EasyDecisionExceptionInterface::class,
    'StepTheFkUp\EasyDecision\Interfaces\EasyDecisionExceptionInterface',
    false
);
