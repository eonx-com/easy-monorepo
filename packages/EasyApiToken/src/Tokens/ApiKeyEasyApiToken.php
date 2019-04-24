<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Tokens;

use StepTheFkUp\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface;

final class ApiKeyEasyApiToken implements ApiKeyEasyApiTokenInterface
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * ApiKeyEasyApiToken constructor.
     *
     * @param string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Get API key.
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Get token payload.
     *
     * @return mixed[]
     */
    public function getPayload(): array
    {
        return ['api_key' => $this->apiKey];
    }
}

\class_alias(
    ApiKeyEasyApiToken::class,
    'LoyaltyCorp\EasyApiToken\Tokens\ApiKeyEasyApiToken',
    false
);
