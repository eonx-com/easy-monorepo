<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tokens;

use EonX\EasyApiToken\Interfaces\Tokens\ApiKeyInterface;

final class ApiKey implements ApiKeyInterface
{
    public function __construct(
        private string $apiKey,
    ) {
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getOriginalToken(): string
    {
        return $this->apiKey;
    }

    public function getPayload(): array
    {
        return [
            'api_key' => $this->apiKey,
        ];
    }
}
