<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Tests\Decoders;

use LoyaltyCorp\EasyApiToken\Decoders\ApiKeyAsBasicAuthUsernameDecoder;
use LoyaltyCorp\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface;
use LoyaltyCorp\EasyApiToken\Tests\AbstractTestCase;

final class ApiKeyAsBasicAuthUsernameDecoderTest extends AbstractTestCase
{
    /**
     * ApiKeyAsBasicAuthUsernameDecoder should return null if Authorization header not set.
     *
     * @return void
     */
    public function testApiKeyAsBasicAuthUsernameNullIfAuthorizationHeaderNotSet(): void
    {
        self::assertNull((new ApiKeyAsBasicAuthUsernameDecoder())->decode($this->createServerRequest()));
    }

    /**
     * ApiKeyAsBasicAuthUsernameDecoder should return null if Authorization header doesn't start with "Basic ".
     *
     * @return void
     */
    public function testApiKeyAsBasicAuthUsernameNullIfDoesntStartWithBasic(): void
    {
        self::assertNull((new ApiKeyAsBasicAuthUsernameDecoder())->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'SomethingElse'
        ])));
    }

    /**
     * ApiKeyAsBasicAuthUsernameDecoder should return null if Authorization header doesn't contain only api key.
     *
     * @return void
     */
    public function testApiKeyAsBasicAuthUsernameNullIfNotOnlyApiKeyProvided(): void
    {
        $tests = [
            '',
            ':',
            ':password',
            'api-key:password'
        ];

        foreach ($tests as $test) {
            self::assertNull((new ApiKeyAsBasicAuthUsernameDecoder())->decode($this->createServerRequest([
                'HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode($test)
            ])));
        }
    }

    /**
     * ApiKeyAsBasicAuthUsernameDecoder should return ApiKeyEasyApiToken and expected api key.
     *
     * @return void
     */
    public function testApiKeyAsBasicAuthUsernameReturnEasyApiTokenSuccessfully(): void
    {
        // Value in header => [expectedUsername, expectedPassword]
        $tests = [
            'api-key' => ['api-key'],
            'api-key:' => ['api-key'],
            'api-key: ' => ['api-key'],
            'api-key:     ' => ['api-key']
        ];

        foreach ($tests as $test => $expected) {
            /** @var \LoyaltyCorp\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface $token */
            $token = (new ApiKeyAsBasicAuthUsernameDecoder())->decode($this->createServerRequest([
                'HTTP_AUTHORIZATION' => \sprintf('Basic %s', \base64_encode($test))
            ]));

            self::assertInstanceOf(ApiKeyEasyApiTokenInterface::class, $token);
            self::assertEquals($expected[0], $token->getPayload()['api_key']);
        }
    }
}

\class_alias(
    ApiKeyAsBasicAuthUsernameDecoderTest::class,
    'StepTheFkUp\EasyApiToken\Tests\Decoders\ApiKeyAsBasicAuthUsernameDecoderTest',
    false
);
