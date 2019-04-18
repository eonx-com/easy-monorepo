<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Interfaces;

interface EasyRepositoryExceptionInterface
{
    // Marker for all exceptions of this package.
}

\class_alias(
    EasyRepositoryExceptionInterface::class,
    'LoyaltyCorp\EasyRepository\Interfaces\EasyRepositoryExceptionInterface',
    false
);
