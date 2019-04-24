<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Interfaces\Tokens;

use StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenInterface;

interface ApiKeyEasyApiTokenInterface extends EasyApiTokenInterface
{
    /**
     * Get API key.
     *
     * @return string
     */
    public function getApiKey(): string;
}

\class_alias(
    ApiKeyEasyApiTokenInterface::class,
    'LoyaltyCorp\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface',
    false
);
