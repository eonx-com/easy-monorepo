<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Interfaces\Tokens\Factories;

use StepTheFkUp\ApiToken\Tokens\JwtApiToken;

interface JwtApiTokenFactoryInterface
{
    /**
     * Create JwtApiToken from given string.
     *
     * @param string $token
     *
     * @return \StepTheFkUp\ApiToken\Tokens\JwtApiToken
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidApiTokenFromRequestException
     */
    public function createFromString(string $token): JwtApiToken;
}
