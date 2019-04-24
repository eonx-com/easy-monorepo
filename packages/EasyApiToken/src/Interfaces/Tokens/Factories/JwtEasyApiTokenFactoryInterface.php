<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Interfaces\Tokens\Factories;

use LoyaltyCorp\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;

interface JwtEasyApiTokenFactoryInterface
{
    /**
     * Create JwtEasyApiToken from given string.
     *
     * @param string $token
     *
     * @return \LoyaltyCorp\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
     */
    public function createFromString(string $token): JwtEasyApiTokenInterface;
}

\class_alias(
    JwtEasyApiTokenFactoryInterface::class,
    'StepTheFkUp\EasyApiToken\Interfaces\Tokens\Factories\JwtEasyApiTokenFactoryInterface',
    false
);
