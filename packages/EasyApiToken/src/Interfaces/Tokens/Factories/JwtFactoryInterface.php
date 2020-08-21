<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Tokens\Factories;

use EonX\EasyApiToken\Interfaces\Tokens\JwtInterface;

/**
 * @deprecated since 2.4. Will be removed in 3.0.
 */
interface JwtFactoryInterface
{
    public function createFromString(string $token): JwtInterface;
}

\class_alias(JwtFactoryInterface::class, JwtEasyApiTokenFactoryInterface::class);
