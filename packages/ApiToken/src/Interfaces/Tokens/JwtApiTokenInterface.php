<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Interfaces\Tokens;

use StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface;

interface JwtApiTokenInterface extends ApiTokenInterface
{
    /**
     * Get value for given claim.
     *
     * @param string $claim
     *
     * @return mixed
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException If claim not found on token
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
