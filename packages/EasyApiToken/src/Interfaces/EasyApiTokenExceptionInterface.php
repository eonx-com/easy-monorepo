<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Interfaces;

interface EasyApiTokenExceptionInterface
{
    // Marker for all exceptions of this package.
}

\class_alias(
    EasyApiTokenExceptionInterface::class,
    'StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenExceptionInterface',
    false
);
