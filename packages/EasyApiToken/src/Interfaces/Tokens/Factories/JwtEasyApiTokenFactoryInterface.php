<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Tokens\Factories;

use EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;

interface JwtEasyApiTokenFactoryInterface
{
    public function createFromString(string $token): JwtEasyApiTokenInterface;
}
