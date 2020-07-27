<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Tokens;

use EonX\EasyApiToken\Interfaces\ApiTokenInterface;

interface ApiKeyInterface extends ApiTokenInterface
{
    public function getApiKey(): string;
}

\class_alias(ApiKeyInterface::class, ApiKeyEasyApiTokenInterface::class);
