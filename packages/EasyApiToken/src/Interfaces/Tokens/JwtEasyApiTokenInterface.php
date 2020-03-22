<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Tokens;

use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;

interface JwtEasyApiTokenInterface extends EasyApiTokenInterface
{
    /**
     * @return mixed
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException If claim not found on token
     */
    public function getClaim(string $claim);

    /**
     * Will convert stdClass to array.
     *
     * @return mixed
     */
    public function getClaimForceArray(string $claim);

    public function hasClaim(string $claim): bool;
}
