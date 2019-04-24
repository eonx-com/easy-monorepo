<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Interfaces\Tokens;

use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenInterface;

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
    'StepTheFkUp\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface',
    false
);
