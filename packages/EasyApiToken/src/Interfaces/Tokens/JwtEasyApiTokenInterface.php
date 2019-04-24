<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Interfaces\Tokens;

use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenInterface;

interface JwtEasyApiTokenInterface extends EasyApiTokenInterface
{
    /**
     * Get value for given claim.
     *
     * @param string $claim
     *
     * @return mixed
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException If claim not found on token
     */
    public function getClaim(string $claim);

    /**
     * Check if token has given claim.
     *
     * @param string $claim
     *
     * @return bool
     */
    public function hasClaim(string $claim): bool;
}

\class_alias(
    JwtEasyApiTokenInterface::class,
    'StepTheFkUp\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface',
    false
);
