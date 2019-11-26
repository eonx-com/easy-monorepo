<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Tokens;

use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;

interface BasicAuthEasyApiTokenInterface extends EasyApiTokenInterface
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
