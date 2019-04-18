<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyIdentity\Interfaces;

interface IdentityServiceExceptionInterface
{
    // Marker for all identity exceptions.
}

\class_alias(
    IdentityServiceExceptionInterface::class,
    'LoyaltyCorp\EasyIdentity\Interfaces\IdentityServiceExceptionInterface',
    false
);
