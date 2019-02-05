<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tokens;

use StepTheFkUp\ApiToken\Interfaces\Tokens\BasicAuthApiTokenInterface;
use StepTheFkUp\ApiToken\Traits\ApiTokenTrait;

final class BasicAuthApiToken extends AbstractClassAsStrategyApiToken implements BasicAuthApiTokenInterface
{
    use ApiTokenTrait;

    /**
     * Get password from payload.
     *
     * @return string
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\EmptyRequiredPayloadException
     */
    public function getPassword(): string
    {
        return $this->getRequiredPayload('password', $this->getPayload());
    }

    /**
     * Get username from payload.
     *
     * @return string
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\EmptyRequiredPayloadException
     */
    public function getUsername(): string
    {
        return $this->getRequiredPayload('username', $this->getPayload());
    }
}