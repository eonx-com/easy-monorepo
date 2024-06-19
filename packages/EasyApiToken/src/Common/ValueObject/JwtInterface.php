<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\ValueObject;

interface JwtInterface extends ApiTokenInterface
{
    /**
     * @throws \EonX\EasyApiToken\Common\Exception\InvalidArgumentException If claim not found on token
     */
    public function getClaim(string $claim): mixed;

    /**
     * Will convert stdClass to array.
     */
    public function getClaimForceArray(string $claim): mixed;

    public function hasClaim(string $claim): bool;
}
