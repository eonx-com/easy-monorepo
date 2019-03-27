<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests\Tokens;

use StepTheFkUp\ApiToken\Tests\AbstractTestCase;
use StepTheFkUp\ApiToken\Tokens\ApiKeyApiToken;

final class ApiKeyApiTokenTest extends AbstractTestCase
{
    /**
     * ApiKeyApiToken should return the same api key as given in input payload.
     *
     * @return void
     */
    public function testGetApiKeySuccessfully(): void
    {
        self::assertEquals('api-key', $this->createApiKeyApiToken()->getApiKey());
    }

    /**
     * BasicAuthToken should return same values from getters and payload for api_key.
     *
     * @return void
     */
    public function testGettersShouldReturnSameValueAsPayload(): void
    {
        $token = $this->createApiKeyApiToken();

        self::assertEquals($token->getApiKey(), $token->getPayload()['api_key']);
    }

    /**
     * Create ApiKeyApiToken.
     *
     * @return \StepTheFkUp\ApiToken\Tokens\ApiKeyApiToken
     */
    private function createApiKeyApiToken(): ApiKeyApiToken
    {
        return new ApiKeyApiToken('api-key');
    }
}
