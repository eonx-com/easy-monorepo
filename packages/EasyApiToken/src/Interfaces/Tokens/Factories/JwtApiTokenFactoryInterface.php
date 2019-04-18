<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Interfaces\Tokens\Factories;

use StepTheFkUp\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;

interface JwtEasyApiTokenFactoryInterface
{
    /**
     * Create JwtEasyApiToken from given string.
     *
     * @param string $token
     *
     * @return \StepTheFkUp\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface
     *
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
     */
    public function createFromString(string $token): JwtEasyApiTokenInterface;
}

\class_alias(
    JwtEasyApiTokenFactoryInterface::class,
    'LoyaltyCorp\EasyApiToken\Interfaces\Tokens\Factories\JwtEasyApiTokenFactoryInterface',
    false
);
