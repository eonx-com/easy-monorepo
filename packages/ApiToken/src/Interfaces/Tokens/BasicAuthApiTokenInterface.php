<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Interfaces\Tokens;

use StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface;

interface BasicAuthApiTokenInterface extends ApiTokenInterface
{
    /**
     * Get password from payload.
     *
     * @return string
     */
    public function getPassword(): string;

    /**
     * Get username from payload.
     *
     * @return string
     */
    public function getUsername(): string;
}
