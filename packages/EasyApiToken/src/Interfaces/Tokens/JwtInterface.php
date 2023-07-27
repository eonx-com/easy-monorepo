<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Tokens;

use EonX\EasyApiToken\Interfaces\ApiTokenInterface;

interface JwtInterface extends ApiTokenInterface
{
    /**
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException If claim not found on token
     */
    public function getClaim(string $claim): mixed;

    /**
     * Will convert stdClass to array.
     */
    public function getClaimForceArray(string $claim): mixed;

    public function hasClaim(string $claim): bool;
}
