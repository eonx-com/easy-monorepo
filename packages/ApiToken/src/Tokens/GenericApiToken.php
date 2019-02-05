<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tokens;

use StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface;

final class GenericApiToken implements ApiTokenInterface
{
    /**
     * @var mixed[]
     */
    private $payload;

    /**
     * @var string
     */
    private $strategy;

    /**
     * GenericApiToken constructor.
     *
     * @param mixed[] $payload
     * @param string $strategy
     */
    public function __construct(array $payload, string $strategy)
    {
        $this->payload = $payload;
        $this->strategy = $strategy;
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
     * Get token strategy.
     *
     * @return string
     */
    public function getStrategy(): string
    {
        return $this->strategy;
    }
}