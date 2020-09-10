<?php

declare(strict_types=1);

namespace EonX\EasyIdentity\Interfaces;

interface IdentityServiceNamesInterface
{
    /**
     * @var string
     */
    public const SERVICE_AUTH0 = 'auth0';

    /**
     * @var string[]
     */
    public const SERVICES = [self::SERVICE_AUTH0];
}
