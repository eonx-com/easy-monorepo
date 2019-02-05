<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tokens;

use StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface;

abstract class AbstractClassAsStrategyApiToken implements ApiTokenInterface
{
    /**
     * @var mixed[]
     */
    private $payload;

    /**
     * AbstractClassAsStrategyApiToken constructor.
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
     * Get token strategy.
     *
     * @return string
     */
    public function getStrategy(): string
    {
        return \get_class($this);
    }
}