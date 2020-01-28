<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tokens;

use EonX\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface;

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
     * Get original string token.
     *
     * @return string
     */
    public function getOriginalToken(): string
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
