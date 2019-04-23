<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Tests\Encoders;

use LoyaltyCorp\EasyApiToken\Encoders\ApiKeyAsBasicAuthUsernameEncoder;
use LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException;
use LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use LoyaltyCorp\EasyApiToken\Tests\AbstractTestCase;
use LoyaltyCorp\EasyApiToken\Tokens\ApiKeyEasyApiToken;
use LoyaltyCorp\EasyApiToken\Tokens\JwtEasyApiToken;

final class ApiKeyAsBasicAuthUsernameEncoderTest extends AbstractTestCase
{
    /**
     * ApiKeyAsBasicAuthUsernameEncoder should throw exception if api key in given token is empty.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
     */
    public function testEmptyApiKeyException(): void
    {
        $this->expectException(UnableToEncodeEasyApiTokenException::class);

        (new ApiKeyAsBasicAuthUsernameEncoder())->encode(new ApiKeyEasyApiToken(''));
    }

    /**
     * ApiKeyAsBasicAuthUsernameEncoder should encode successfully api keys.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
     */
    public function testApiKeyEncodeTokenSuccessfully(): void
    {
        $tests = [
            'apikey',
            'api-key',
            'Sp3c|@l_cH\\aracters'
        ];

        foreach ($tests as $test) {
            $token = (new ApiKeyAsBasicAuthUsernameEncoder())->encode(new ApiKeyEasyApiToken($test));

            self::assertEquals(\base64_encode($test), $token);
        }
    }

    /**
     * ApiKeyAsBasicAuthUsernameEncoder should throw exception if given token isn't ApiKeyEasyApiTokenInterface.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
     */
    public function testInvalidTokenException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ApiKeyAsBasicAuthUsernameEncoder())->encode(new JwtEasyApiToken([]));
    }
}

\class_alias(
    ApiKeyAsBasicAuthUsernameEncoderTest::class,
    'StepTheFkUp\EasyApiToken\Tests\Encoders\ApiKeyAsBasicAuthUsernameEncoderTest',
    false
);
