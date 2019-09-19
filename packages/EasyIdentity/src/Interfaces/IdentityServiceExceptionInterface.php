<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Interfaces;

interface IdentityServiceExceptionInterface
{
    // Marker for all identity exceptions.
}

\class_alias(
    IdentityServiceExceptionInterface::class,
    \StepTheFkUp\EasyIdentity\Interfaces\IdentityServiceExceptionInterface::class,
    false
);
