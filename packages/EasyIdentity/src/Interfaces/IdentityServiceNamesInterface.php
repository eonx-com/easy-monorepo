<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyIdentity\Interfaces;

interface IdentityServiceNamesInterface
{
    /** @var string */
    public const SERVICE_AUTH0 = 'auth0';

    /** @var string[] */
    public const SERVICES = [
        self::SERVICE_AUTH0
    ];
}

\class_alias(
    IdentityServiceNamesInterface::class,
    'LoyaltyCorp\EasyIdentity\Interfaces\IdentityServiceNamesInterface',
    false
);
