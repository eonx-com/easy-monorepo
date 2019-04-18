<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Interfaces;

interface EasyApiTokenExceptionInterface
{
    // Marker for all exceptions of this package.
}

\class_alias(
    EasyApiTokenExceptionInterface::class,
    'LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenExceptionInterface',
    false
);
