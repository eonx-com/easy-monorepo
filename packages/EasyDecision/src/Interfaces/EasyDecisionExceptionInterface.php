<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces;

interface EasyDecisionExceptionInterface
{
    // Marker for all exceptions of this package.
}

\class_alias(
    EasyDecisionExceptionInterface::class,
    'LoyaltyCorp\EasyDecision\Interfaces\EasyDecisionExceptionInterface',
    false
);
