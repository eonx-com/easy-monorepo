<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tokens;

use StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException;
use StepTheFkUp\ApiToken\Interfaces\Tokens\JwtApiTokenInterface;

final class JwtApiToken implements JwtApiTokenInterface
{
    /**
     * @var mixed[]
     */
    private $payload;

    /**
     * JwtApiToken constructor.
     *
     * @param mixed[] $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Get token payload.
     *
     * @return mixed[]
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * Get value for given claim.
     *
     * @param string $claim
     *
     * @return mixed
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException If claim not found on token
     */
    public function getClaim(string $claim)
    {
        if ($this->hasClaim($claim)) {
            return $this->payload[$claim];
        }

        throw new InvalidArgumentException(\sprintf('In "%s", claim "%s" not found', \get_class($this), $claim));
    }

    /**
     * Check if token has given claim.
     *
     * @param string $claim
     *
     * @return bool
     */
    public function hasClaim(string $claim): bool
    {
        return isset($this->payload[$claim]);
    }
}
