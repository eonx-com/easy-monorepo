<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Decoders;

use EonX\EasyApiToken\Decoders\ApiKeyAsBasicAuthUsernameDecoder;
use EonX\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface;
use EonX\EasyApiToken\Tests\AbstractTestCase;

final class ApiKeyAsBasicAuthUsernameDecoderTest extends AbstractTestCase
{
    public function testApiKeyAsBasicAuthUsernameNullIfAuthorizationHeaderNotSet(): void
    {
        self::assertNull((new ApiKeyAsBasicAuthUsernameDecoder())->decode($this->createServerRequest()));
    }

    public function testApiKeyAsBasicAuthUsernameNullIfDoesntStartWithBasic(): void
    {
        self::assertNull((new ApiKeyAsBasicAuthUsernameDecoder())->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'SomethingElse',
        ])));
    }

    public function testApiKeyAsBasicAuthUsernameNullIfNotOnlyApiKeyProvided(): void
    {
        $tests = [
            '',
            ':',
            ':password',
            'api-key:password',
        ];

        foreach ($tests as $test) {
            self::assertNull((new ApiKeyAsBasicAuthUsernameDecoder())->decode($this->createServerRequest([
                'HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode($test),
            ])));
        }
    }

    public function testApiKeyAsBasicAuthUsernameReturnEasyApiTokenSuccessfully(): void
    {
        // Value in header => [expectedUsername, expectedPassword]
        $tests = [
            'api-key' => ['api-key'],
            'api-key:' => ['api-key'],
            'api-key: ' => ['api-key'],
            'api-key:     ' => ['api-key'],
        ];

        foreach ($tests as $test => $expected) {
            /** @var \EonX\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface $token */
            $token = (new ApiKeyAsBasicAuthUsernameDecoder())->decode($this->createServerRequest([
                'HTTP_AUTHORIZATION' => \sprintf('Basic %s', \base64_encode($test)),
            ]));

            self::assertInstanceOf(ApiKeyEasyApiTokenInterface::class, $token);
            self::assertEquals($expected[0], $token->getPayload()['api_key']);
        }
    }
}
