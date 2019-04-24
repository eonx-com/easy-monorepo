<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyRepository\Interfaces;

interface EasyRepositoryExceptionInterface
{
    // Marker for all exceptions of this package.
}

\class_alias(
    EasyRepositoryExceptionInterface::class,
    'StepTheFkUp\EasyRepository\Interfaces\EasyRepositoryExceptionInterface',
    false
);
