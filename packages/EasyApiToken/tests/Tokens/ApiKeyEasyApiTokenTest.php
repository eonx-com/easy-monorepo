<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Tests\Tokens;

use LoyaltyCorp\EasyApiToken\Tests\AbstractTestCase;
use LoyaltyCorp\EasyApiToken\Tokens\ApiKeyEasyApiToken;

final class ApiKeyEasyApiTokenTest extends AbstractTestCase
{
    /**
     * ApiKeyEasyApiToken should return the same api key as given in input payload.
     *
     * @return void
     */
    public function testGetApiKeySuccessfully(): void
    {
        self::assertEquals('api-key', $this->createApiKeyEasyApiToken()->getApiKey());
    }

    /**
     * BasicAuthToken should return same values from getters and payload for api_key.
     *
     * @return void
     */
    public function testGettersShouldReturnSameValueAsPayload(): void
    {
        $token = $this->createApiKeyEasyApiToken();

        self::assertEquals($token->getApiKey(), $token->getPayload()['api_key']);
    }

    /**
     * Create ApiKeyEasyApiToken.
     *
     * @return \LoyaltyCorp\EasyApiToken\Tokens\ApiKeyEasyApiToken
     */
    private function createApiKeyEasyApiToken(): ApiKeyEasyApiToken
    {
        return new ApiKeyEasyApiToken('api-key');
    }
}

\class_alias(
    ApiKeyEasyApiTokenTest::class,
    'StepTheFkUp\EasyApiToken\Tests\Tokens\ApiKeyEasyApiTokenTest',
    false
);
