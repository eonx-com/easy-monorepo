<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tokens;

use StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface;

final class JwtApiToken implements ApiTokenInterface
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
}