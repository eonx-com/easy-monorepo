<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tokens;

use StepTheFkUp\ApiToken\Interfaces\Tokens\ApiKeyApiTokenInterface;

final class ApiKeyApiToken implements ApiKeyApiTokenInterface
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * ApiKeyApiToken constructor.
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