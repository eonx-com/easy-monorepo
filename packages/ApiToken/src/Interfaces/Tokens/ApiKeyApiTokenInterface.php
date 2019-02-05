<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Interfaces\Tokens;

use StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface;

interface ApiKeyApiTokenInterface extends ApiTokenInterface
{
    /**
     * Get API key.
     *
     * @return string
     */
    public function getApiKey(): string;
}