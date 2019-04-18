<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Tests\Encoders;

use StepTheFkUp\EasyApiToken\Encoders\ApiKeyAsBasicAuthUsernameEncoder;
use StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException;
use StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use StepTheFkUp\EasyApiToken\Tests\AbstractTestCase;
use StepTheFkUp\EasyApiToken\Tokens\ApiKeyEasyApiToken;
use StepTheFkUp\EasyApiToken\Tokens\JwtEasyApiToken;

final class ApiKeyAsBasicAuthUsernameEncoderTest extends AbstractTestCase
{
    /**
     * ApiKeyAsBasicAuthUsernameEncoder should throw exception if api key in given token is empty.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
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
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
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
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
     */
    public function testInvalidTokenException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ApiKeyAsBasicAuthUsernameEncoder())->encode(new JwtEasyApiToken([]));
    }
}

\class_alias(
    ApiKeyAsBasicAuthUsernameEncoderTest::class,
    'LoyaltyCorp\EasyApiToken\Tests\Encoders\ApiKeyAsBasicAuthUsernameEncoderTest',
    false
);
