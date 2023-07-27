<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Tokens;

use EonX\EasyApiToken\Interfaces\ApiTokenInterface;

interface ApiKeyInterface extends ApiTokenInterface
{
    public function getApiKey(): string;
}
