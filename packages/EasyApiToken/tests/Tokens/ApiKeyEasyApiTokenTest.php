<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Tokens;

use EonX\EasyApiToken\Tests\AbstractTestCase;
use EonX\EasyApiToken\Tokens\ApiKey;

final class ApiKeyEasyApiTokenTest extends AbstractTestCase
{
    public function testGetApiKeySuccessfully(): void
    {
        self::assertEquals('api-key', $this->createApiKeyEasyApiToken()->getApiKey());
        self::assertEquals('api-key', $this->createApiKeyEasyApiToken()->getOriginalToken());
    }

    public function testGettersShouldReturnSameValueAsPayload(): void
    {
        $token = $this->createApiKeyEasyApiToken();

        self::assertEquals($token->getApiKey(), $token->getPayload()['api_key']);
    }

    private function createApiKeyEasyApiToken(): ApiKey
    {
        return new ApiKey('api-key');
    }
}
