<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\Common\ValueObject;

use EonX\EasyApiToken\Common\ValueObject\ApiKey;
use EonX\EasyApiToken\Tests\Unit\AbstractUnitTestCase;

final class ApiKeyTest extends AbstractUnitTestCase
{
    public function testGetApiKeySuccessfully(): void
    {
        self::assertSame('api-key', $this->createApiKey()->getApiKey());
        self::assertSame('api-key', $this->createApiKey()->getOriginalToken());
    }

    public function testGettersShouldReturnSameValueAsPayload(): void
    {
        $token = $this->createApiKey();

        self::assertSame($token->getApiKey(), $token->getPayload()['api_key']);
    }

    private function createApiKey(): ApiKey
    {
        return new ApiKey('api-key');
    }
}
