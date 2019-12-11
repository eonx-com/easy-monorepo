<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Tokens\Factories;

use EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;

interface JwtEasyApiTokenFactoryInterface
{
    /**
     * Create JwtEasyApiToken from given string.
     *
     * @param string $token
     *
     * @return \EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
     */
    public function createFromString(string $token): JwtEasyApiTokenInterface;
}
