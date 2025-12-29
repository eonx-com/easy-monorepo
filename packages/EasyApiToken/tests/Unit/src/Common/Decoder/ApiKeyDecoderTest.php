<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\Common\Decoder;

use EonX\EasyApiToken\Common\Decoder\ApiKeyDecoder;
use EonX\EasyApiToken\Common\Driver\HashedApiKeyDriver;
use EonX\EasyApiToken\Common\ValueObject\ApiKey;
use EonX\EasyApiToken\Common\ValueObject\HashedApiKey;
use EonX\EasyApiToken\Tests\Unit\AbstractUnitTestCase;

final class ApiKeyDecoderTest extends AbstractUnitTestCase
{
    public function testApiKeyNullIfAuthorizationHeaderNotSet(): void
    {
        self::assertNull($this->getDecoder()->decode($this->createRequest()));
    }

    public function testApiKeyNullIfDoesntStartWithBasic(): void
    {
        self::assertNull($this->getDecoder()->decode($this->createRequest([
            'HTTP_AUTHORIZATION' => 'SomethingElse',
        ])));
    }

    public function testApiKeyNullIfNotOnlyApiKeyProvided(): void
    {
        $tests = ['', ':', ':password', 'api-key:password'];

        foreach ($tests as $test) {
            self::assertNull($this->getDecoder()->decode($this->createRequest([
                'HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode($test),
            ])));
        }
    }

    public function testApiKeyReturnEasyApiTokenSuccessfully(): void
    {
        // Value in header => [expectedUsername, expectedPassword]
        $tests = [
            'api-key' => ['api-key'],
            'api-key:' => ['api-key'],
            'api-key: ' => ['api-key'],
            'api-key:     ' => ['api-key'],
        ];

        foreach ($tests as $test => $expected) {
            /** @var \EonX\EasyApiToken\Common\ValueObject\ApiKey $token */
            $token = $this->getDecoder()
                ->decode($this->createRequest([
                    'HTTP_AUTHORIZATION' => \sprintf('Basic %s', \base64_encode($test)),
                ]));

            self::assertInstanceOf(ApiKey::class, $token);
            self::assertEquals($expected[0], $token->getPayload()['api_key']);
        }
    }

    public function testHashedApiKeyWithInvalidStructureReturnApiKey(): void
    {
        $tokenMissingId = \base64_encode(\json_encode([
            'no-id' => 'my-id',
            'secret' => 'my-secret',
        ]) ?: '');

        $token = $this->getDecoder()
            ->decode($this->createRequest([
                'HTTP_AUTHORIZATION' => \sprintf('Basic %s', \base64_encode($tokenMissingId . ':')),
            ]));

        self::assertInstanceOf(ApiKey::class, $token);
        self::assertEquals($tokenMissingId, $token->getPayload()['api_key']);
    }

    public function testHashedApiKeyWithValidStructureReturnHashedApiKey(): void
    {
        $expected = [
            'id' => 'my-id',
            'secret' => 'my-secret',
            'version' => 'v1',
        ];
        $hashedApiKey = \base64_encode(\json_encode($expected) ?: '');

        $token = $this->getDecoder()
            ->decode($this->createRequest([
                'HTTP_AUTHORIZATION' => \sprintf('Basic %s', \base64_encode($hashedApiKey . ':')),
            ]));

        self::assertInstanceOf(HashedApiKey::class, $token);
        self::assertEquals($expected, $token->getPayload());
    }

    private function getDecoder(): ApiKeyDecoder
    {
        return (new ApiKeyDecoder())->setHashedApiKeyDriver(new HashedApiKeyDriver());
    }
}
