<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Tokens;

use EonX\EasyApiToken\Interfaces\ApiTokenInterface;

interface BasicAuthInterface extends ApiTokenInterface
{
    public function getPassword(): string;

    public function getUsername(): string;
}

\class_alias(BasicAuthInterface::class, BasicAuthEasyApiTokenInterface::class);
