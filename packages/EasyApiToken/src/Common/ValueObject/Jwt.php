<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\ValueObject;

use EonX\EasyApiToken\Common\Exception\InvalidArgumentException;
use Nette\Utils\Json;
use stdClass;

final readonly class Jwt implements ApiTokenInterface
{
    public function __construct(
        private array $payload,
        private string $original,
    ) {
    }

    /**
     * @throws \EonX\EasyApiToken\Common\Exception\InvalidArgumentException If claim not found on token
     */
    public function getClaim(string $claim): mixed
    {
        if ($this->hasClaim($claim)) {
            return $this->payload[$claim];
        }

        throw new InvalidArgumentException(\sprintf('In "%s", claim "%s" not found', self::class, $claim));
    }

    /**
     * Will convert stdClass to array.
     *
     * @throws \EonX\EasyApiToken\Common\Exception\InvalidArgumentException
     * @throws \Nette\Utils\JsonException
     */
    public function getClaimForceArray(string $claim): mixed
    {
        $claim = $this->getClaim($claim);

        if ($claim instanceof stdClass) {
            $claim = Json::decode(Json::encode($claim), true);
        }

        return $claim;
    }

    public function getOriginalToken(): string
    {
        return $this->original;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function hasClaim(string $claim): bool
    {
        return isset($this->payload[$claim]);
    }
}
