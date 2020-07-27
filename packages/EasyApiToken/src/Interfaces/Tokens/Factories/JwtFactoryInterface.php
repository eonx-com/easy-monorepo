<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Tokens\Factories;

use EonX\EasyApiToken\Interfaces\Tokens\JwtInterface;

interface JwtFactoryInterface
{
    public function createFromString(string $token): JwtInterface;
}

\class_alias(JwtFactoryInterface::class, JwtEasyApiTokenFactoryInterface::class);
