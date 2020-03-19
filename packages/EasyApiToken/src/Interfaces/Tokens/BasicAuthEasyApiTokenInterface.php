<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Tokens;

use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;

interface BasicAuthEasyApiTokenInterface extends EasyApiTokenInterface
{
    public function getPassword(): string;

    public function getUsername(): string;
}
