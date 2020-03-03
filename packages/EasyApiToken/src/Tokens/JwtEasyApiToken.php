<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tokens;

use EonX\EasyApiToken\Exceptions\InvalidArgumentException;
use EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;

final class JwtEasyApiToken implements JwtEasyApiTokenInterface
{
    /**
     * @var string
     */
    private $original;

    /**
     * @var mixed[]
     */
    private $payload;

    /**
     * JwtEasyApiToken constructor.
     *
     * @param mixed[] $payload
     * @param string $original
     */
    public function __construct(array $payload, string $original)
    {
        $this->payload = $payload;
        $this->original = $original;
    }

    /**
     * Get value for given claim.
     *
     * @param string $claim
     *
     * @return mixed
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException If claim not found on token
     */
    public function getClaim(string $claim)
    {
        if ($this->hasClaim($claim)) {
            return $this->payload[$claim];
        }

        throw new InvalidArgumentException(\sprintf('In "%s", claim "%s" not found', \get_class($this), $claim));
    }

    /**
     * Get original string token.
     *
     * @return string
     */
    public function getOriginalToken(): string
    {
        return $this->original;
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
