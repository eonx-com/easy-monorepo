<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tokens;

use StepTheFkUp\ApiToken\Interfaces\Tokens\BasicAuthApiTokenInterface;

final class BasicAuthApiToken extends AbstractApiToken implements BasicAuthApiTokenInterface
{
    /**
     * Get password from payload.
     *
     * @return string
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\EmptyRequiredPayloadException
     */
    public function getPassword(): string
    {
        return $this->getRequiredPayload('password');
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
        return $this->getRequiredPayload('username');
    }
}