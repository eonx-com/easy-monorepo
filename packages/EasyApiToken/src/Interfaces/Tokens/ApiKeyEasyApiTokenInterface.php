<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Tokens;

use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;

interface ApiKeyEasyApiTokenInterface extends EasyApiTokenInterface
{
    /**
     * Get API key.
     *
     * @return string
     */
    public function getApiKey(): string;
}
